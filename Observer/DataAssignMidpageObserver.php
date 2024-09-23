<?php

namespace Billink\Billink\Observer;

use Billink\Billink\Gateway\Helper\Workflow;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class DataAssignObserver
 * @package Billink\Billink\Observer
 */
class DataAssignMidpageObserver extends AbstractDataAssignObserver
{
    protected array $additionalInformationList = [
        DataAssignObserver::CUSTOMER_TYPE,
        DataAssignObserver::VALIDATE_ORDER_FLAG,
        DataAssignObserver::INVOICE_EMAIL,
        DataAssignObserver::REFERENCE
    ];

    public function __construct(
        private readonly Workflow $workflowHelper
    ) {
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $paymentInfo = $this->readPaymentModelArgument($observer);
        /** @var CartInterface $quote */
        $quote = $paymentInfo->getQuote();
        $address = $quote->getBillingAddress();
        $company = trim((string)$address->getCompany());
        $customerType = $company ? 'B' : 'P';

        $oldValue = $paymentInfo->getAdditionalInformation(DataAssignObserver::CUSTOMER_TYPE);

        $paymentInfo->setAdditionalInformation(
            DataAssignObserver::CUSTOMER_TYPE,
            $customerType
        );

        $paymentInfo->setAdditionalInformation(
            DataAssignObserver::WORKFLOW_NUMBER,
            $this->workflowHelper->getNumber($customerType)
        );

        //if customer type changed we need to recalculate totals to apply proper billink fee
        if ($oldValue !== $customerType) {
            $paymentInfo->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        }
    }
}
