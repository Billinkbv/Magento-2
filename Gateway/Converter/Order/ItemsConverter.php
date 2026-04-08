<?php

namespace Billink\Billink\Gateway\Converter\Order;

use Billink\Billink\Gateway\Request\OrderItemsDataBuilder;
use Billink\Billink\Model\Billink\Request\Order\ItemFactory;
use Billink\Billink\Model\Billink\Request\Order\ItemInterfaceFactory;
use Billink\Billink\Model\Fee\BillinkFee;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\CalculationFactory;

use function method_exists;
use function round;

class ItemsConverter implements ConverterInterface
{
    private array $items = [];

    public function __construct(
        private readonly TaxHelper $taxData,
        private readonly CalculationFactory $calculationFactory,
        private readonly ItemInterfaceFactory $orderItemFactory,
        private readonly BillinkFee $billinkFee
    ) {
    }

    public function convert(?Order $order = null): array
    {
        $this->items = [];

        if (!$order) {
            return $this->items;
        }

        $orderItems = $order->getItems() ?: $order->getItemsCollection();

        foreach ($orderItems as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $this->addOrderItem($item);

            if ($item->getDiscountAmount()) {
                $this->addDiscountOrderItem($item, $order);
            }
        }

        if ($this->getShippingAmount($order)) {
            $this->addShippingAmountItem($order);
        }

        if ($this->billinkFee->isActive()) {
            $this->addBillinkFeeAmountItem($order);
        }

        $this->addFoomanSurcharge($order);

        return $this->items;
    }

    private function getPriceType(): string
    {
        return $this->taxData->priceIncludesTax() ?
            OrderItemsDataBuilder::PRICEINCL : OrderItemsDataBuilder::PRICEEXCL;
    }

    private function getShippingPriceType(Order $order): string
    {
        return $this->taxData->shippingPriceIncludesTax($order->getStore()) ?
            OrderItemsDataBuilder::PRICEINCL : OrderItemsDataBuilder::PRICEEXCL;
    }

    private function getBillinkFeeType(): string
    {
        return $this->billinkFee->getFeeIncludesTax() ?
            OrderItemsDataBuilder::PRICEINCL : OrderItemsDataBuilder::PRICEEXCL;
    }

    private function getShippingDescription(Order $order): string
    {
        return $order->getShippingDescription() ?: $order->getShippingAddress()->getShippingDescription();
    }

    private function getShippingAmount(Order $order): ?float
    {
        if ($this->taxData->shippingPriceIncludesTax($order->getStore())) {
            return $order->getShippingInclTax() ?: $order->getShippingAddress()->getShippingInclTax();
        }

        return $order->getShippingAmount() ?: $order->getShippingAddress()->getShippingAmount();
    }

    private function addOrderItem(OrderItemInterface $item): void
    {
        $price = $this->taxData->priceIncludesTax() ? $item->getPriceInclTax() : $item->getPrice();

        $orderItem = $this->orderItemFactory->create();
        $orderItem->setCode($item->getSku());
        $orderItem->setDescription($item->getName());
        $orderItem->setQuantity($item->getQty() ?: $item->getQtyOrdered());
        $orderItem->setTaxPercent($item->getTaxPercent() ?: 0);
        $orderItem->setPriceType($this->getPriceType());
        $orderItem->setPrice($price);

        $this->items[] = $orderItem;
    }

    private function addDiscountOrderItem(OrderItemInterface $item, Order $order): void
    {
        $price = 0 - $item->getDiscountAmount();

        $discountOrderItem = $this->orderItemFactory->create();
        $discountOrderItem->setCode($order->getCouponCode());
        $discountOrderItem->setDescription($item->getName());
        $discountOrderItem->setQuantity(1);
        $discountOrderItem->setTaxPercent($item->getTaxPercent() ?: 0);
        $discountOrderItem->setPriceType($this->getPriceType());
        $discountOrderItem->setPrice($price);

        $this->items[] = $discountOrderItem;
    }

    private function addShippingAmountItem(Order $order): void
    {
        $taxCalculation = $this->calculationFactory->create();

        $shippingTaxClass = $this->taxData->getShippingTaxClass($order->getStore());
        $taxRequest = $taxCalculation
            ->getRateRequest($order->getShippingAddress(), null, null, $order->getStore())
            ->setProductClassId($shippingTaxClass);

        $taxRate = $taxRequest ? $taxCalculation->getRate($taxRequest) : 0;

        $discountOrderItem = $this->orderItemFactory->create();
        $discountOrderItem->setCode('shipping');
        $discountOrderItem->setDescription($this->getShippingDescription($order));
        $discountOrderItem->setQuantity(1);
        $discountOrderItem->setTaxPercent($taxRate);
        $discountOrderItem->setPriceType($this->getShippingPriceType($order));
        $discountOrderItem->setPrice($this->getShippingAmount($order));

        $this->items[] = $discountOrderItem;
    }

    private function addBillinkFeeAmountItem(Quote|Order $orderData): void
    {
        if (!$this->billinkFee->isActive()) {
            return;
        }

        $taxRate = $this->billinkFee->getTaxRate($orderData) ?: 0;
        $baseAmount = $this->billinkFee->getBaseAmount($orderData);

        if ($baseAmount > 0) {
            $billinkFeeItem = $this->orderItemFactory->create();
            $billinkFeeItem->setCode('billink_fee');
            $billinkFeeItem->setDescription($this->billinkFee->getFeeLabel());
            $billinkFeeItem->setQuantity(1);
            $billinkFeeItem->setTaxPercent($taxRate);
            $billinkFeeItem->setPriceType($this->getBillinkFeeType());
            $billinkFeeItem->setPrice($baseAmount);

            $this->items[] = $billinkFeeItem;
        }
    }

    /**
     * If the Fooman Surcharge plugin is installed, try to fetch the surcharge
     */
    private function addFoomanSurcharge(Quote|Order $orderData): void
    {
        if ($orderData instanceof Quote) {
            //As seen in Fooman\SurchargePayment\Plugin\SurchargePreview
            if ($orderData->isVirtual()) {
                $address = $orderData->getBillingAddress();
            } else {
                $address = $orderData->getShippingAddress();
            }

            $extensionAttributes = $address->getExtensionAttributes();
        } elseif ($orderData instanceof Order) {
            $extensionAttributes = $orderData->getExtensionAttributes();
        } else {
            return;
        }

        if (
            $extensionAttributes
            && method_exists($extensionAttributes, 'getFoomanTotalGroup')
            && $foomanTotalGroup = $extensionAttributes->getFoomanTotalGroup()
        ) {
            //If Fooman Surcharges is installed, this function should be part of the Order-/Address- ExtensionInterface
            foreach ($foomanTotalGroup->getItems() as $item) {
                if ($item->getAmount() > 0) {
                    $billinkFeeItem = $this->orderItemFactory->create();

                    $taxRate = 0;
                    if ($item->getTaxAmount()) {
                        $taxRate = round(
                            ($item->getBaseTaxAmount() + $item->getBaseAmount()) / $item->getBaseAmount(),
                            2
                        );
                    }

                    $priceType = $item->getBasePrice() ? OrderItemsDataBuilder::PRICEINCL :
                        OrderItemsDataBuilder::PRICEEXCL;

                    $billinkFeeItem->setCode('fooman_surcharge');
                    $billinkFeeItem->setDescription($item->getLabel());
                    $billinkFeeItem->setQuantity(1);
                    $billinkFeeItem->setTaxPercent($taxRate);
                    $billinkFeeItem->setPriceType($priceType);
                    $billinkFeeItem->setPrice($item->getBaseAmount());

                    $this->items[] = $billinkFeeItem;
                }
            }
        }
    }
}
