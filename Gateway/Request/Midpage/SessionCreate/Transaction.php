<?php

namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Billink\Billink\Model\Fee\BillinkFee;
use Billink\Billink\Model\LocalStorage;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\CalculationFactory;

class Transaction implements BuilderInterface
{
    private array $items = [];

    public function __construct(
        protected readonly LocalStorage $localStorage,
        private readonly TaxHelper $taxData,
        private readonly CalculationFactory $calculationFactory,
        private readonly BillinkFee $billinkFee
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
        $this->items = [];
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
            $this->addItem($item);
            if ($item->getDiscountAmount() > 0) {
                $this->addItemDiscount($item, $order);
            }
        }
        if ($order->getShippingAmount() > 0) {
            $taxCalculation = $this->calculationFactory->create();

            $shippingTaxClass = $this->taxData->getShippingTaxClass($order->getStore());
            $taxRequest = $taxCalculation
                ->getRateRequest($order->getShippingAddress(), null, null, $order->getStore())
                ->setProductClassId($shippingTaxClass);

            $taxRate = $taxRequest ? $taxCalculation->getRate($taxRequest) : 0;

            $this->items[] = [
                'code' => '0001',
                'name' => (string)$order->getShippingDescription(),
                'description' => '',
                'totalProductAmount' => (string)$order->getShippingInclTax(),
                'productAmount' => (string)$order->getShippingInclTax(),
                'productTaxAmount' => (string)$order->getShippingTaxAmount(),
                'taxRate' => (string)($taxRate),
                'quantity' => '1',
            ];
        }
        $this->prepareFeeItem($order);
        return $this->items;
    }

    protected function addItem(OrderItemInterface $orderItem): void
    {
        $price = $orderItem->getPriceInclTax();
        $rowTotal = $orderItem->getRowTotalInclTax();
        $taxAmount = ($orderItem->getRowTotalInclTax() - $orderItem->getRowTotal()) / $orderItem->getQtyOrdered();

        $this->items[] = [
            'code' => (string)$orderItem->getSku(),
            'name' => (string)$orderItem->getName(),
            'description' => (string)$orderItem->getDescription(),
            //'productIdentifiers' => [], // @todo add identifiers
            'totalProductAmount' => (string)$rowTotal,
            'productAmount' => (string)$price,
            'productTaxAmount' => (string)$taxAmount,
            'taxRate' => (string)($orderItem->getTaxPercent() ?: 0),
            'quantity' => (string)$orderItem->getQtyOrdered(),
        ];
    }

    private function addItemDiscount(OrderItemInterface $item, OrderInterface $order): void
    {
        $name = $order->getDiscountDescription() ?: $order->getData('coupon_rule_name');
        if (!$name && $order->getCouponCode()) {
            $name = 'Discount code: ' . $order->getCouponCode();
        }
        if (!$name) {
            $name = 'Discount';
        }

        $price = $item->getDiscountAmount();

        $rowTotalInclTax = $item->getRowTotalInclTax();
        $rowTotal = $item->getRowTotal();
        $taxAmount = $item->getTaxAmount();
        $diff = 0;
        if ($item->getDiscountAmount() > 0) {
            // Check for tax difference
            $baseTax = $rowTotalInclTax - $rowTotal;
            // Check if tax amount has been re-calculated after discount is applied
            if ($baseTax > $taxAmount) {
                $diff = $baseTax - $taxAmount;
                $price += $diff;
            }
        }
        $price *= -1;
        $diff *= -1;

        $this->items[] = [
            'code' => '0002',
            'name' => $name,
            'description' => '',
            'totalProductAmount' => (string)$price,
            'productAmount' => (string)$price,
            'productTaxAmount' => (string)$diff,
            'taxRate' => (string)($item->getTaxPercent() ?: 0),
            'quantity' => '1',
        ];
    }

    private function prepareFeeItem(OrderInterface $order): void
    {
        if (!$this->billinkFee->isActive()) {
            return ;
        }
        $orderAmount = $order->getData('billink_fee_amount');
        $configAmount = $this->billinkFee->getBaseAmount($order);
        if (!$this->billinkFee->getFeeIncludesTax()) {
            // Add taxes to the total field
            $configAmount += $order->getData('billink_fee_amount_tax');
        }

        if ($configAmount > 0 && $orderAmount > 0) {
            $taxRate = $this->billinkFee->getTaxRate($order) ?: 0;
            $this->items[] = [
                'code' => 'billink_fee',
                'name' => $this->billinkFee->getFeeLabel(),
                'description' => '',
                'totalProductAmount' => (string)$configAmount,
                'productAmount' => (string)$configAmount,
                'productTaxAmount' => (string)$order->getData('billink_fee_amount_tax'),
                'taxRate' => (string)$taxRate,
                'quantity' => '1',
            ];
        }
    }
}
