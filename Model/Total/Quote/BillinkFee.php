<?php

namespace Billink\Billink\Model\Total\Quote;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Model\Total\AvailabilityTrait;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

/**
 * Class BillinkFee
 * @package Billink\Billink\Model\Total\Quote
 */
class BillinkFee extends AbstractTotal
{
    use AvailabilityTrait;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrencyInterface;

    /**
     * @var \Billink\Billink\Helper\Fee
     */
    private $fee;

    /**
     * @var Config
     */
    private $config;

    /**
     * BillinkFee constructor.
     * @param Config $config
     * @param \Billink\Billink\Model\Fee\BillinkFee $fee
     * @param PriceCurrencyInterface $priceCurrencyInterface
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrencyInterface,
        \Billink\Billink\Model\Fee\BillinkFee $fee,
        Config $config

    ) {
        $this->priceCurrencyInterface = $priceCurrencyInterface;
        $this->fee = $fee;
        $this->config = $config;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() != Address::TYPE_SHIPPING
            || $quote->isVirtual()
            || !$this->isApplicable($quote)
        ) {
            return $this;
        }

        $feeInfo = $this->fee->getFeeInfo($quote);

        $baseAmount = $feeInfo->getBaseAmount();
        $amount = $this->priceCurrencyInterface->convert($baseAmount);

        $baseAmountTax = $feeInfo->getBaseAmountTax();
        $amountTax = $this->priceCurrencyInterface->convert($baseAmountTax);

        $total->setBaseTotalAmount('billink_fee', $baseAmount);
        $total->setTotalAmount('billink_fee', $amount);

        $total->setBaseBillinkFeeAmount($baseAmount);
        $total->setBillinkFeeAmount($amount);

        $total->setBaseBillinkFeeAmountTax($baseAmountTax);
        $total->setBillinkFeeAmountTax($amountTax);

        $total->setBaseTaxAmount($total->getBaseTaxAmount() + $baseAmountTax);
        $total->setTaxAmount($total->getTaxAmount() + $amountTax);

        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $baseAmount + $baseAmountTax);
        $total->setGrandTotal($total->getGrandTotal() + $amount + $amountTax);

        $quote->setBaseBillinkFeeAmount($baseAmount);
        $quote->setBillinkFeeAmount($amount);

        $quote->setBaseBillinkFeeAmountTax($baseAmountTax);
        $quote->setBillinkFeeAmountTax($amountTax);

        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     */
    public function fetch(
        Quote $quote,
        Total $total
    ) {
        if ($this->isApplicable($quote)) {
            return [
                'code' => 'billink_fee',
                'title' => $this->config->getFeeLabel(),
                'value' => $total->getBillinkFeeAmount()
            ];
        }
        return null;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return $this->config->getFeeLabel();
    }

}