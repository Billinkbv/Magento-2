<?php

namespace Billink\Billink\Model\Total\Invoice;

use Billink\Billink\Gateway\Config\BasePaymentConfig as Config;
use Billink\Billink\Model\Total\AvailabilityTrait;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

use function min;

class AbstractBillinkFee extends AbstractTotal
{
    use AvailabilityTrait;

    public function __construct(
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($data);
    }

    public function collect(Invoice $invoice): static
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
