<?php

namespace Billink\Billink\Model\Fee;

use Billink\Billink\Helper\Fee as FeeHelper;
use Billink\Billink\Helper\Quote as QuoteHelper;
use Magento\Framework\DataObject;
use Magento\Tax\Model\CalculationFactory;

/**
 * Class BillinkFee
 * @package Billink\Billink\Model\Fee
 */
class BillinkFee
{
    /**
     * @var Fee
     */
    private $feeHelper;

    /**
     * @var CalculationFactory
     */
    private $calculationFactory;

    /**
     * @var Quote
     */
    private $quoteHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * BillinkFee constructor.
     * @param FeeHelper $feeHelper
     * @param CalculationFactory $calculationFactory
     * @param QuoteHelper $quoteHelper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        FeeHelper $feeHelper,
        CalculationFactory $calculationFactory,
        QuoteHelper $quoteHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->feeHelper = $feeHelper;
        $this->calculationFactory = $calculationFactory;
        $this->quoteHelper = $quoteHelper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return DataObject
     */
    public function getFeeInfo($quote)
    {
        $fee = new DataObject();

        $baseAmount = $this->getBaseAmount($quote);
        $baseAmountTax = $this->getBaseAmountTax($baseAmount, $quote);

        $fee->setBaseAmount($baseAmount);
        $fee->setBaseAmountTax($baseAmountTax);

        return $fee;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->feeHelper->getIsFeeActive();
    }

    /**
     * @return string
     */
    public function getFeeLabel()
    {
        return $this->feeHelper->getFeeLabel();
    }


    /**
     * @return bool
     */
    public function getFeeIncludesTax()
    {
        return $this->feeHelper->getFeeIncludesTax();
    }


    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     */
    public function getBaseAmount($quote)
    {
        $workflowType = $this->quoteHelper->getWorkflowType($quote);

        if (!$workflowType && !$workflowType = $this->getWorkflowTypeFromRequest()) {
            return 0;
        }

        $quoteTotal = $this->quoteHelper->getTotalInclTax($quote);

        return $this->feeHelper->getFeeAmount($quoteTotal, $workflowType, $quote->getBillingAddress()->getCountryId());
    }

    /**
     * @param float $baseAmount
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    public function getBaseAmountTax($baseAmount, $quote)
    {
        if (!$baseAmount) {
            return 0;
        }

        $taxCalculation = $this->calculationFactory->create();

        $taxRate = $this->getTaxRate($quote);

        return $taxCalculation->calcTaxAmount($baseAmount, $taxRate, $this->feeHelper->getFeeIncludesTax());
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    public function getTaxRate($quote)
    {
        $taxCalculation = $this->calculationFactory->create();

        return $taxCalculation->getRate($this->getTaxRequest($quote));
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     */
    private function getTaxRequest($quote)
    {
        $taxCalculation = $this->calculationFactory->create();

        return $taxCalculation->getRateRequest(
            $quote->getShippingAddress(),
            $quote->getBillingAddress(),
            $quote->getCustomerTaxClassId(),
            $quote->getStore()
        )->setProductClassId($this->feeHelper->getFeeTaxClass());
    }

    private function getWorkflowTypeFromRequest()
    {
        $payment = $this->request->getParam('payment');

        if (!isset($payment[\Billink\Billink\Observer\DataAssignObserver::CUSTOMER_TYPE])) {
            return false;
        }

        return $payment[\Billink\Billink\Observer\DataAssignObserver::CUSTOMER_TYPE];
    }
}
