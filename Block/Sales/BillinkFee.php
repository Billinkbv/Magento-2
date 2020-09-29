<?php

namespace Billink\Billink\Block\Sales;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Model\Ui\ConfigProvider;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

/**
 * Class BillinkFee
 * @package Billink\Billink\Block\Sales
 */
class BillinkFee extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $source;

    /**
     * BillinkFee constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();

        $this->source = $parent->getSource();

        if ($this->isApplicable()) {
            $amount = $this->source->getBaseBillinkFeeAmount() - $this->source->getBaseBillinkFeeTaxAmount();

            if ($amount > 0) {
                $fee = new DataObject(
                    [
                        'code' => ConfigProvider::CODE,
                        'strong' => false,
                        'value' => $amount,
                        'label' => $this->config->getFeeLabel(),
                    ]
                );

                $parent->addTotalBefore($fee, 'grand_total');
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function isApplicable()
    {
        if (!$payment = $this->source->getPayment()) {
            $payment = $this->source->getOrder()->getPayment();
        }

        return ConfigProvider::CODE == $payment->getMethod();
    }
}