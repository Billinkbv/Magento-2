<?php

namespace Billink\Billink\Model\Total\Invoice;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Model\Total\AvailabilityTrait;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class BillinkFee
 * @package Billink\Billink\Model\Total\Invoice
 */
class BillinkFee extends AbstractTotal
{
    use AvailabilityTrait;

    /**
     * @var Config
     */
    private $config;

    /**
     * BillinkFee constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $invoice->setBillinkFeeAmount($order->getBillinkFeeAmount());
        $invoice->setBaseBillinkFeeAmount($order->getBaseBillinkFeeAmount());

        $invoice->setBillinkFeeAmountTax($order->getBillinkFeeAmountTax());
        $invoice->setBaseBillinkFeeAmountTax($order->getBaseBillinkFeeAmountTax());

        if ($this->isApplicable($order)) {

            $allowedTax = $order->getTaxAmount() - $order->getTaxInvoiced() - $invoice->getTaxAmount();
            $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced() - $invoice->getBaseTaxAmount();

            $totalTaxAmount = min($invoice->getBillinkFeeAmountTax(), $allowedTax);
            $baseTotalTaxAmount = min($invoice->getBaseBillinkFeeAmountTax(), $allowedBaseTax);

            $invoice->setTaxAmount($totalTaxAmount);
            $invoice->setBaseTaxAmount($baseTotalTaxAmount);

            $invoice->setGrandTotal(
                $invoice->getGrandTotal() + $invoice->getBillinkFeeAmount() + $totalTaxAmount
            );

            $invoice->setBaseGrandTotal(
                $invoice->getBaseGrandTotal() + $invoice->getBaseBillinkFeeAmount() + $baseTotalTaxAmount
            );
        }
        return $this;
    }
}
