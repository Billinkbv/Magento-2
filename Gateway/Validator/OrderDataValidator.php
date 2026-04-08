<?php

namespace Billink\Billink\Gateway\Validator;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Helper\Calculator;
use Billink\Billink\Gateway\Helper\Gateway as GatewayHelper;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Helper\Number as NumberHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Psr\Log\LoggerInterface;

use function __;
use function array_key_exists;

class OrderDataValidator extends AbstractValidator
{
    public const INDEX_FLAG_VALIDATION = 'validation';

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        private readonly Config $config,
        private readonly GatewayCommand $checkCommand,
        private readonly GatewayCommand $orderCommand,
        private readonly SubjectReader $subjectReader,
        private readonly WorkflowHelper $workflowHelper,
        private readonly NumberHelper $numberHelper,
        private readonly Calculator $orderTotalCalculator,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($resultFactory);
    }

    /**
     * Performs domain-related validation for business object
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $payment = $this->subjectReader->readPayment($validationSubject);
        $paymentAI = $this->subjectReader->readPaymentAdditionalInformation($validationSubject);

        $validateFlag = isset($paymentAI[DataAssignObserver::VALIDATE_ORDER_FLAG]) && (bool) $paymentAI[DataAssignObserver::VALIDATE_ORDER_FLAG];

        $result = true;
        $resultMsg = [];

        if (!$validateFlag) {
            return $this->createResult($result, $resultMsg);
        }

        try {
            foreach ($this->getValidators() as $validator) {
                if (!$validator($validationSubject)) {
                    $result = false;
                }
            }
        } catch (Exception $e) {
            $result = false;
            $resultMsg[] = $e->getMessage();
        }

        if (!$result) {
            $payment->unsAdditionalInformation(GatewayHelper::CHECKUUID);
        }

        return $this->createResult($result, $resultMsg);
    }

    protected function getValidators(): array
    {
        return [
            function ($validationSubject) {
                if (!$this->config->getIsTotalcheckActive()) {
                    return true;
                }

                $payment = $this->subjectReader->readPayment($validationSubject);
                $orderData = $payment->getOrder() ?: $payment->getQuote();

                $calculatedTotal = $this->orderTotalCalculator->calculateOrderTotal($orderData);
                $quoteTotal = $orderData->getGrandTotal() ?: 0.00;

                if (!$this->numberHelper->floatsAreEqual($calculatedTotal, $quoteTotal)) {
                    $this->logger->error(
                        __(
                            'Order totals do not match. ID: %d ; Calculated: %s ; QuoteTotal: %s ',
                            $orderData->getId(),
                            $calculatedTotal,
                            $quoteTotal
                        )
                    );

                    throw new LocalizedException(__('Order totals do not match'));
                }

                return true;
            },
            function ($validationSubject) {
                $paymentAI = $this->subjectReader->readPaymentAdditionalInformation($validationSubject);
                $workflowType = $this->subjectReader->readPaymentWorkflowType($validationSubject);

                if (
                    !$this->workflowHelper->getIsWithCheck($workflowType)
                    || array_key_exists(GatewayHelper::CHECKUUID, $paymentAI)
                ) {
                    return true;
                }

                $this->checkCommand->execute($validationSubject);

                $validationSubject[self::INDEX_FLAG_VALIDATION] = true;
                $this->orderCommand->execute($validationSubject);

                return true;
            }
        ];
    }
}
