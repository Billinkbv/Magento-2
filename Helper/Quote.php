<?php

namespace Billink\Billink\Helper;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Quote\Model\Quote as MagentoQuote;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Sales\Model\Order;

class Quote
{
    public function __construct(
        private readonly SubjectReader $subjectReader
    ) {
    }

    public function getWorkflowType(MagentoQuote|Order $quote): string
    {
        $payment = $quote->getPayment();

        return $this->subjectReader->readPaymentWorkflowType(['payment' => $payment]);
    }

    public function getTotalInclTax(MagentoQuote|Order $quoteData): float
    {
        $grandTotal = 0.0;

        foreach ($this->getQuoteItems($quoteData) as $item) {
            $itemPrice = $item->getPriceInclTax() * ($item->getQty() ?: $item->getQtyOrdered());
            $itemDiscount = $item->getDiscountAmount();
            $grandTotal += ($itemPrice - $itemDiscount);
        }

        return $grandTotal;
    }

    public function getQuoteItems(MagentoQuote|Order $quoteData): Collection|array|false
    {
        if ($quoteData instanceof MagentoQuote) {
            return $quoteData->getItemsCollection();
        }

        if ($quoteData instanceof Order) {
            return $quoteData->getItems();
        }

        return false;
    }
}
