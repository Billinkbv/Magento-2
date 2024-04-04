<?php

namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Billink\Billink\Model\LocalStorage;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
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
            'orderItems' => $this->prepareItems($order)
        ];
        return ['transaction' => $data];
    }

    private function prepareItems(OrderInterface $order): array
    {
        $data = [];
        foreach ($order->getItems() as $item) {
            // Do not send simple products from configurable, but send simples from bundle
            if ($item->getProductType() === \Magento\Bundle\Model\Product\Type::TYPE_CODE ||
                (
                    $item->getParentItem() !== null
                    && $item->getParentItem()->getProductType() === Configurable::TYPE_CODE
                )
            ) {
                continue;
            }
            $rowTotalInclTax = $item->getRowTotalInclTax();
            $rowTotal = $item->getRowTotal();
            $baseItemPrice = $item->getBasePriceInclTax();
            $taxAmount = $item->getTaxAmount();
            if ($item->getDiscountAmount() > 0) {
                // Check for tax difference
                $baseTax = $rowTotalInclTax - $rowTotal;
                // Check if tax amount has been re-calculated after discount is applied
                if ($baseTax > $taxAmount) {
                    $diff = ($baseTax - $taxAmount) / $item->getQtyOrdered();
                    $baseItemPrice -= $diff;
                }
            }
            $data[] = [
                'code' => (string)$item->getSku(),
                'name' => (string)$item->getName(),
                'description' => (string)$item->getDescription(),
                //'productIdentifiers' => [], // @todo add identifiers
                'totalProductAmount' => (string)$rowTotalInclTax,
                'productAmount' => (string)$baseItemPrice,
                'productTaxAmount' => (string)$taxAmount,
                'taxRate' => (string)$item->getTaxPercent(),
                'quantity' => (string)$item->getQtyOrdered(),
            ];
        }
        if ($order->getShippingAmount() > 0) {
            $data[] = [
                'code' => '0001',
                'name' => (string)$order->getShippingDescription(),
                'description' => '',
                'totalProductAmount' => (string)$order->getShippingInclTax(),
                'productAmount' => (string)$order->getShippingAmount(),
                'productTaxAmount' => (string)$order->getShippingTaxAmount(),
                'taxRate' => (string)(round($order->getShippingTaxAmount() * 100 / $order->getShippingInclTax(), 2)),
                'quantity' => '1',
            ];
        }
        if ($order->getDiscountAmount() < 0) {
            $value = ($order->getDiscountAmount() + $order->getDiscountTaxCompensationAmount());
            $name = (string)$order->getDiscountDescription();
            if (!$name) {
                $name = 'Discount';
            }
            $data[] = [
                'code' => '0002',
                'name' => $name,
                'description' => '',
                'totalProductAmount' => (string)$value,
                'productAmount' => (string)$value,
                'productTaxAmount' => '0',
                'taxRate' => '0',
                'quantity' => '1',
            ];
        }
        return $data;
    }
}
