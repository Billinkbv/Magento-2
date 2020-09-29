<?php

namespace Billink\Billink\Gateway\Validator;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Helper\Calculator;
use Billink\Billink\Gateway\Helper\Gateway as GatewayHelper;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Helper\Number as NumberHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * Class OrderDataValidator
 * @package Billink\Billink\Gateway\Validator
 */
class OrderDataValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    const INDEX_FLAG_VALIDATION = 'validation';

    /**
     * @var GatewayCommand
     */
    private $checkCommand;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Workflow
     */
    private $workflowHelper;

    /**
     * @var GatewayCommand
     */
    private $orderCommand;

    /**
     * @var Number
     */
    private $numberHelper;

    /**
     * @var Calculator
     */
    private $orderTotalCalculator;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Config
     */
    private $config;

    /**
     * OrderDataValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param Config $config
     * @param GatewayCommand $checkCommand
     * @param GatewayCommand $orderCommand
     * @param SubjectReader $subjectReader
     * @param WorkflowHelper $workflowHelper
     * @param NumberHelper $numberHelper
     * @param Calculator $orderTotalCalculator
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Config $config,
        GatewayCommand $checkCommand,
        GatewayCommand $orderCommand,
        SubjectReader $subjectReader,
        WorkflowHelper $workflowHelper,
        NumberHelper $numberHelper,
        Calculator $orderTotalCalculator,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->checkCommand = $checkCommand;
        $this->orderCommand = $orderCommand;
        $this->subjectReader = $subjectReader;
        $this->workflowHelper = $workflowHelper;
        $this->numberHelper = $numberHelper;
        $this->orderTotalCalculator = $orderTotalCalculator;
        $this->logger = $logger;

        parent::__construct($resultFactory);
    }


    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $payment = $this->subjectReader->readPayment($validationSubject);
        $paymentAI = $this->subjectReader->readPaymentAdditionalInformation($validationSubject);

        $validateFlag = isset($paymentAI[DataAssignObserver::VALIDATE_ORDER_FLAG])
            ? (bool)$paymentAI[DataAssignObserver::VALIDATE_ORDER_FLAG] : false;

        $result = true;
        $resultMsg = [];

        if (!$validateFlag) {
            return $this->createResult($result, $resultMsg);
        }

        try {
            foreach ($this->getValidators() as $validator) {
                $validatorResult = $validator($validationSubject);

                if (!$validatorResult) {
                    $result = false;
                }
            }
        } catch (\Exception $e) {
            $result = false;
            $resultMsg[] = $e->getMessage();
        }

        if (!$result) {
            $payment->unsAdditionalInformation(GatewayHelper::CHECKUUID);
        }

        return $this->createResult($result, $resultMsg);
    }

    /**
     * @return array
     */
    protected function getValidators()
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
                        __('Order totals do not match. ID: %d ; Calculated: %s ; QuoteTotal: %s ', $orderData->getId(),
                            $calculatedTotal, $quoteTotal)
                    );

                    throw new LocalizedException(__('Order totals do not match'));
                }

                return true;
            },
            function ($validationSubject) {
                $paymentAI = $this->subjectReader->readPaymentAdditionalInformation($validationSubject);
                $workflowType = $this->subjectReader->readPaymentWorkflowType($validationSubject);

                if (!$this->workflowHelper->getIsWithCheck($workflowType) ||
                    array_key_exists(GatewayHelper::CHECKUUID, $paymentAI)
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