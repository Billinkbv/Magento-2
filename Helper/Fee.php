<?php

namespace Billink\Billink\Helper;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;

/**
 * Class Fee
 * @package Billink\Billink\Helper
 */
class Fee
{
    const INDEX_WORKFLOW_TYPE = 'workflow_type';
    const INDEX_TOTAL_FROM = 'total_from';
    const INDEX_TOTAL_TO = 'total_to';
    const INDEX_AMOUNT = 'amount';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Workflow
     */
    private $workflowHelper;

    /**
     * Fee constructor.
     * @param Config $config
     * @param WorkflowHelper $workflowHelper
     */
    public function __construct(
        Config $config,
        WorkflowHelper $workflowHelper
    ) {
        $this->config = $config;
        $this->workflowHelper = $workflowHelper;
    }

    /**
     * @return bool
     */
    public function getIsFeeActive()
    {
        return (bool)$this->config->getIsFeeActive();
    }

    /**
     * @return string
     */
    public function getFeeLabel()
    {
        return $this->config->getFeeLabel();
    }


    /**
     * @return string
     */
    public function getFeeTaxClass()
    {
        return $this->config->getFeeTaxClass();
    }

    /**
     * @return bool
     */
    public function getFeeIncludesTax()
    {
        return (bool)$this->config->getFeeType();
    }

    /**
     * @param float $total
     * @param string $workflowType
     * @return mixed
     */
    public function getFeeAmount($total, $workflowType = WorkflowHelper::TYPE_PRIVATE)
    {
        $feeRanges = $this->config->getFeeRange();
        $result = 0.00;

        foreach ($feeRanges as $feeRange) {
            if (
                $feeRange[self::INDEX_WORKFLOW_TYPE] == $this->workflowHelper->getOptionKey($workflowType) &&
                $this->isFeeInRange($total, $feeRange)
            ) {
                $configFee = $feeRange[self::INDEX_AMOUNT];

                if (substr($configFee, -1) == '%') {
                    $percentage = (float)$configFee;
                    $result = $total * ($percentage / 100);
                } else {
                    $result = (float)$configFee;
                }
            }
        }

        return $result;
    }

    /**
     * @param float $total
     * @param array $range
     * @return bool
     */
    public function isFeeInRange($total, array $range)
    {
        $min = $range[self::INDEX_TOTAL_FROM];
        $max = $range[self::INDEX_TOTAL_TO];

        if ((!$min && !$max) || (!$min && $total <= $max) || (!$max && $total >= $min)) {
            return true;
        }

        if ($min && $max) {
            return $min <= $total && $max >= $total;
        }

        return false;
    }
}