<?php

namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Billink\Billink\Model\LocalStorage;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderItemInterface;

class Transaction implements BuilderInterface
{
    public function __construct(
        protected readonly LocalStorage $localStorage
    ) {
    }

    /**
     * Fields converted to string so it will be sent as sting in the json.
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $paymentOrder = $paymentDO->getOrder();
        // The order above doesn't cover what we need with interfaces, so we need an original object here.
        $order = $this->localStorage->getOrder();
        if (!$order) {
            throw new LocalizedException(__('Midpage payment is not available at this point.'));
        }
        $shipping = $paymentDO->getOrder()->getShippingAddress();
        $purchaseCountry = 'NL';
        if ($shipping) {
            $purchaseCountry = $shipping->getCountryId();
        }

        $data = [
            'totalAmount' => (string)$paymentOrder->getGrandTotalAmount(),
            'totalTaxAmount' => (string)$order->getTaxAmount(),
            'purchaseCountry' => $purchaseCountry,
            'purchaseCurrency' => $order->getOrderCurrencyCode(),
            'orderNumber' => $order->getIncrementId(),
            'orderItems' => $this->prepareItems($paymentOrder->getItems())
        ];
        return ['transaction' => $data];
    }

    private function prepareItems(array $items): array
    {
        $data = [];
        /** @var OrderItemInterface $item */
        foreach ($items as $item) {
            // Do not send simple products from configurable, but send simples from bundle
            if ($item->getProductType() === \Magento\Bundle\Model\Product\Type::TYPE_CODE ||
                (
                    $item->getParentItem() !== null
                    && $item->getParentItem()->getProductType() === Configurable::TYPE_CODE
                )
            ) {
                continue;
            }
            $data[] = [
                'code' => (string)$item->getSku(),
                'name' => (string)$item->getName(),
                'description' => (string)$item->getDescription(),
                //'productIdentifiers' => [], // @todo add identifiers
                'totalProductAmount' => (string)$item->getRowTotalInclTax(),
                'productAmount' => (string)$item->getBasePriceInclTax(),
                'productTaxAmount' => (string)$item->getTaxAmount(),
                'taxRate' => (string)$item->getTaxPercent(),
                'quantity' => (string)$item->getQtyOrdered(),
            ];
        }
        return $data;
    }
}