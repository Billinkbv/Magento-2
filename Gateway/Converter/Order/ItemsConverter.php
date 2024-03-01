<?php

namespace Billink\Billink\Gateway\Converter\Order;

use Billink\Billink\Gateway\Request\OrderItemsDataBuilder;
use Billink\Billink\Model\Billink\Request\Order\ItemInterfaceFactory;
use Billink\Billink\Model\Fee\BillinkFee;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\CalculationFactory;

/**
 * Class ItemsConverter
 * @package Billink\Billink\Gateway\Converter\Order
 */
class ItemsConverter implements ConverterInterface
{
    /**
     * @var \Billink\Billink\Model\Billink\Request\Order\ItemFactory
     */
    private $orderItemFactory;

    /**
     * @var Data
     */
    private $taxData;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var CalculationFactory
     */
    private $calculationFactory;

    /**
     * @var BillinkFee
     */
    private $billinkFee;

    /**
     * ItemsConverter constructor.
     * @param TaxHelper $taxData
     * @param CalculationFactory $calculationFactory
     * @param ItemInterfaceFactory $orderItemFactory
     * @param BillinkFee $billinkFee
     */
    public function __construct(
        TaxHelper $taxData,
        CalculationFactory $calculationFactory,
        ItemInterfaceFactory $orderItemFactory,
        BillinkFee $billinkFee
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->taxData = $taxData;
        $this->calculationFactory = $calculationFactory;
        $this->billinkFee = $billinkFee;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function convert($order)
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

    /**
     * @return string
     */
    private function getPriceType()
    {
        return $this->taxData->priceIncludesTax() ?
            OrderItemsDataBuilder::PRICEINCL : OrderItemsDataBuilder::PRICEEXCL;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    private function getShippingPriceType($order)
    {
        return $this->taxData->shippingPriceIncludesTax($order->getStore()) ?
            OrderItemsDataBuilder::PRICEINCL : OrderItemsDataBuilder::PRICEEXCL;
    }

    /**
     * @return string
     */
    private function getBillinkFeeType()
    {
        return $this->billinkFee->getFeeIncludesTax() ?
            OrderItemsDataBuilder::PRICEINCL : OrderItemsDataBuilder::PRICEEXCL;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    private function getShippingDescription($order)
    {
        return $order->getShippingDescription() ?: $order->getShippingAddress()->getShippingDescription();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    private function getShippingAmount($order)
    {
        if ($this->taxData->shippingPriceIncludesTax($order->getStore())) {
            return $order->getShippingInclTax() ?: $order->getShippingAddress()->getShippingInclTax();
        }

        return $order->getShippingAmount() ?: $order->getShippingAddress()->getShippingAmount();
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return void
     */
    private function addOrderItem($item)
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

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    private function addDiscountOrderItem($item, $order)
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

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    private function addShippingAmountItem($order)
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

    /**
     * @param \Magento\Sales\Model\Order|\Magento\Quote\Model\Quote $orderData
     * @return void
     */
    private function addBillinkFeeAmountItem($orderData)
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
     *
     * @param \Magento\Sales\Model\Order|\Magento\Quote\Model\Quote $orderData
     * @return void
     */
    private function addFoomanSurcharge($orderData)
    {
        if ($orderData instanceof \Magento\Quote\Model\Quote) {
            //As seen in Fooman\SurchargePayment\Plugin\SurchargePreview
            if ($orderData->isVirtual()) {
                $address = $orderData->getBillingAddress();
            } else {
                $address = $orderData->getShippingAddress();
            }

            $extensionAttributes = $address->getExtensionAttributes();
        } elseif ($orderData instanceof \Magento\Sales\Model\Order) {
            $extensionAttributes = $orderData->getExtensionAttributes();
        } else {
            return;
        }

        if ($extensionAttributes) {
            //If Fooman Surcharges is installed, this function should be part of the Order-/Address- ExtensionInterface
            if (method_exists($extensionAttributes, 'getFoomanTotalGroup')) {
                if ($foomanTotalGroup = $extensionAttributes->getFoomanTotalGroup()) {
                    foreach ($foomanTotalGroup->getItems() as $item) {
                        if ($item->getAmount() > 0) {
                            $billinkFeeItem = $this->orderItemFactory->create();

                            $taxRate = 0;
                            if ($item->getTaxAmount()) {
                                $taxRate = round(($item->getBaseTaxAmount() + $item->getBaseAmount()) / $item->getBaseAmount(), 2);
                            }

                            $priceType = $item->getBasePrice() ? OrderItemsDataBuilder::PRICEINCL : OrderItemsDataBuilder::PRICEEXCL;

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
    }
}
