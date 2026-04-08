<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Converter\Order\ConverterInterface;
use Billink\Billink\Gateway\Request\OrderItemsDataBuilder;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

use function round;

class Calculator
{
    public function __construct(
        private readonly ConverterInterface $orderItemsConverter
    ) {
    }

    public function calculateOrderTotal(Quote|Order $orderData): float|int
    {
        $total = 0;

        foreach ($this->orderItemsConverter->convert($orderData) as $item) {
            if ($item->getPriceType() === OrderItemsDataBuilder::PRICEINCL) {
                $price = $item->getPrice();
            } else {
                $price = $item->getPrice() + ($item->getPrice() / 100 * $item->getTaxPercent());
            }

            $total += round($item->getQuantity() * $price, 2);
        }

        return $total;
    }
}
