<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class CompanyDataBuilder implements BuilderInterface
{
    const COMPANYNAME = 'COMPANYNAME';
    const CHAMBEROFCOMMERCE = 'CHAMBEROFCOMMERCE';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * CompanyDataBuilder constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->subjectReader->readPayment($buildSubject);
        $workflowType = $this->subjectReader->readPaymentWorkflowType($buildSubject);

        $orderData = $payment->getQuote() ?: $payment->getOrder();
        $billingAddress = $orderData->getBillingAddress();

        if (WorkflowHelper::TYPE_BUSINESS !== $workflowType) {
            return [];
        }

        $result = [
            self::COMPANYNAME => $billingAddress->getCompany(),
            self::CHAMBEROFCOMMERCE => $this->subjectReader->readPaymentAIField(
                DataAssignObserver::CHAMBER_OF_COMMERCE,
                $buildSubject
            )
        ];

        return $result;
    }
}