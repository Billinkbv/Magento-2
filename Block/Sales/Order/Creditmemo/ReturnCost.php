<?php

namespace Billink\Billink\Block\Sales\Order\Creditmemo;

use Billink\Billink\Model\Ui\ConfigProvider;
use Magento\Backend\Block\Template;
use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;

class ReturnCost extends Template
{
    /**
     * Source object
     */
    protected DataObject $source;

    /**
     * Initialize creditmemo adjustment totals
     */
    public function initTotals(): self
    {
        $parent = $this->getParentBlock();
        $this->source = $parent->getSource();
        $total = new DataObject(
            ['code' => 'billink_adjustments', 'block_name' => $this->getNameInLayout()]
        );
        $parent->removeTotal('billink_fee_amount');
        $parent->addTotal($total);

        return $this;
    }

    /**
     * Format value based on order currency
     */
    public function formatValue(?float $value): string
    {
        /** @var Order $order */
        $order = $this->getSource()->getOrder();

        return $order->getOrderCurrency()->formatPrecision(
            $value,
            2,
            ['display' => CurrencyData::NO_SYMBOL],
            false,
            false
        );
    }

    /**
     * Get source object
     */
    public function getSource(): DataObject
    {
        return $this->source;
    }

    public function isBillink(): bool
    {
        $method = $this->source?->getOrder()?->getPayment()?->getMethod();

        return $method === ConfigProvider::CODE_MIDPAGE;
    }
}
