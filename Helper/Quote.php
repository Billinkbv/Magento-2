<?php

namespace Billink\Billink\Helper;

use Billink\Billink\Gateway\Helper\SubjectReader;

/**
 * Class Quote
 * @package Billink\Billink\Helper
 */
class Quote
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Quote constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function getWorkflowType($quote)
    {
        $payment = $quote->getPayment();

        return $this->subjectReader->readPaymentWorkflowType(['payment' => $payment]);
    }

    /**
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $quoteData
     * @return float
     */
    public function getTotalInclTax($quoteData)
    {
        $grandTotal = 0;

        foreach ($this->getQuoteItems($quoteData) as $item) {
            $itemPrice = $item->getPriceInclTax() * ($item->getQty() ?: $item->getQtyOrdered());
            $itemDiscount = $item->getDiscountAmount();
            $grandTotal += ($itemPrice - $itemDiscount);
        }

        return $grandTotal;
    }

    public function getQuoteItems($quoteData)
    {
        if ($quoteData instanceof \Magento\Quote\Model\Quote) {
            return $quoteData->getItemsCollection();
        }

        if ($quoteData instanceof \Magento\Sales\Model\Order) {
            return $quoteData->getItems();
        }

        return false;
    }
}