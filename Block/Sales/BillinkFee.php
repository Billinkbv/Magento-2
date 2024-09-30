<?php

namespace Billink\Billink\Block\Sales;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Config\MidpageConfig;
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
     * @var \Magento\Sales\Model\Order
     */
    private $source;

    public function __construct(
        Template\Context $context,
        private readonly Config $config,
        private readonly MidpageConfig $midpageConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
                if ($this->getMethodCode() === ConfigProvider::CODE_MIDPAGE) {
                    $label = $this->midpageConfig->getFeeLabel();
                } else {
                    $label = $this->config->getFeeLabel();
                }
                $fee = new DataObject(
                    [
                        'code' => ConfigProvider::CODE,
                        'strong' => false,
                        'value' => $amount,
                        'label' => $label,
                    ]
                );

                $parent->addTotalBefore($fee, 'grand_total');
            }
        }

        return $this;
    }

    private function isApplicable(): bool
    {
        return ConfigProvider::CODE === $this->getMethodCode()
            || ConfigProvider::CODE_MIDPAGE === $this->getMethodCode();
    }

    private function getMethodCode(): string
    {
        $payment = $this->source->getPayment();
        if (!$payment) {
            $payment = $this->source->getOrder()->getPayment();
        }
        return (string)$payment->getMethod();
    }

}
