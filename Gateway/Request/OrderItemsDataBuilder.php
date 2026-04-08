<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Converter\Order\ConverterInterface;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

use function round;

class OrderItemsDataBuilder implements BuilderInterface
{
    public const ORDERITEMS = 'ORDERITEMS';
    public const ITEM = 'ITEM';
    public const CODE = 'CODE';
    public const DESCRIPTION = 'DESCRIPTION';
    public const ITEMQUANTITY = 'ITEMQUANTITY';
    public const PRICEINCL = 'PRICEINCL';
    public const PRICEEXCL = 'PRICEEXCL';
    public const BTW = 'BTW';

    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly ConverterInterface $orderItemsConverter
    ) {
    }

    public function build(array $buildSubject): array
    {
        $payment = $this->subjectReader->readPayment($buildSubject);
        $orderData = $payment->getQuote() ?: $payment->getOrder();

        $items = $this->orderItemsConverter->convert($orderData);

        $result = [
            self::ORDERITEMS => []
        ];

        foreach ($items as $index => $item) {
            $result[self::ORDERITEMS][$index . self::ITEM] = [
                self::CODE => $item->getCode(),
                self::DESCRIPTION => $item->getDescription(),
                self::ITEMQUANTITY => $item->getQuantity(),
                self::BTW => $item->getTaxPercent() ?: 0,
                $item->getPriceType() => round($item->getPrice(), 2)
            ];
        }

        return $result;
    }
}
