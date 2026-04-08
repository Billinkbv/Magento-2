<?php

namespace Billink\Billink\Model\Fee;

use Billink\Billink\Helper\Fee as FeeHelper;
use Billink\Billink\Helper\Quote as QuoteHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Tax\Model\CalculationFactory;

class BillinkFee
{
    public function __construct(
        private readonly FeeHelper $feeHelper,
        private readonly CalculationFactory $calculationFactory,
        private readonly QuoteHelper $quoteHelper,
        private readonly RequestInterface $request
    ) {
    }

    public function getFeeInfo(Quote $quote): DataObject
    {
        $fee = new DataObject();

        $baseAmount = $this->getBaseAmount($quote);
        $baseAmountTax = $this->getBaseAmountTax($baseAmount, $quote);

        $fee->setBaseAmount($baseAmount);
        $fee->setBaseAmountTax($baseAmountTax);

        return $fee;
    }

    public function isActive(): bool
    {
        return $this->feeHelper->getIsFeeActive();
    }

    public function getFeeLabel(): string
    {
        return $this->feeHelper->getFeeLabel();
    }

    public function getFeeIncludesTax(): bool
    {
        return $this->feeHelper->getFeeIncludesTax();
    }

    public function getBaseAmount(Quote|Order $quote): float
    {
        $workflowType = $this->quoteHelper->getWorkflowType($quote);

        if (!$workflowType && !$workflowType = $this->getWorkflowTypeFromRequest()) {
            return 0.0;
        }

        $quoteTotal = $this->quoteHelper->getTotalInclTax($quote);

        return $this->feeHelper->getFeeAmount($quoteTotal, $workflowType, $quote->getBillingAddress()->getCountryId());
    }

    public function getBaseAmountTax(float $baseAmount, Quote $quote): float
    {
        if (!$baseAmount) {
            return 0.0;
        }

        $taxCalculation = $this->calculationFactory->create();

        $taxRate = $this->getTaxRate($quote);

        return $taxCalculation->calcTaxAmount($baseAmount, $taxRate, $this->feeHelper->getFeeIncludesTax());
    }

    public function getTaxRate(Quote $quote): float
    {
        return $this->calculationFactory->create()->getRate($this->getTaxRequest($quote));
    }

    private function getTaxRequest(Quote $quote): mixed
    {
        return $this->calculationFactory->create()->getRateRequest(
            $quote->getShippingAddress(),
            $quote->getBillingAddress(),
            $quote->getCustomerTaxClassId(),
            $quote->getStore()
        )->setProductClassId($this->feeHelper->getFeeTaxClass());
    }

    private function getWorkflowTypeFromRequest(): mixed
    {
        $payment = $this->request->getParam('payment');

        return $payment[DataAssignObserver::CUSTOMER_TYPE] ?? false;
    }
}
