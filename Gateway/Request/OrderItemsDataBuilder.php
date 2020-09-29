<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Converter\Order\ConverterInterface;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class OrderItemsDataBuilder implements BuilderInterface
{
    const ORDERITEMS = 'ORDERITEMS';
    const ITEM = 'ITEM';
    const CODE = 'CODE';
    const DESCRIPTION = 'DESCRIPTION';
    const ITEMQUANTITY = 'ITEMQUANTITY';
    const PRICEINCL = 'PRICEINCL';
    const PRICEEXCL = 'PRICEEXCL';
    const BTW = 'BTW';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ConverterInterface
     */
    private $orderItemsConverter;

    /**
     * OrderItemsDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param ConverterInterface $orderItemsConverter
     */
    public function __construct(
        SubjectReader $subjectReader,
        ConverterInterface $orderItemsConverter
    ) {
        $this->subjectReader = $subjectReader;
        $this->orderItemsConverter = $orderItemsConverter;
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