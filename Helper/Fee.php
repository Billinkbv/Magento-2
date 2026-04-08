<?php

namespace Billink\Billink\Helper;

use Billink\Billink\Gateway\Config\BasePaymentConfig as Config;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;

use function array_key_exists;

class Fee
{
    public const COUNTRY = 'country';
    public const INDEX_WORKFLOW_TYPE = 'workflow_type';
    public const INDEX_TOTAL_FROM = 'total_from';
    public const INDEX_TOTAL_TO = 'total_to';
    public const INDEX_AMOUNT = 'amount';

    public function __construct(
        private readonly Config $config,
        private readonly WorkflowHelper $workflowHelper
    ) {
    }

    public function getIsFeeActive(): bool
    {
        return $this->config->getIsFeeActive();
    }

    public function getFeeLabel(): string
    {
        return $this->config->getFeeLabel();
    }

    public function getFeeTaxClass(): string
    {
        return $this->config->getFeeTaxClass();
    }

    public function getFeeIncludesTax(): bool
    {
        return (bool) $this->config->getFeeType();
    }

    public function getFeeAmount(
        float $total,
        string $workflowType = WorkflowHelper::TYPE_PRIVATE,
        ?string $country = 'other'
    ): float {
        $feeRanges = $this->config->getFeeRange();

        foreach ([$country, 'other'] as $customerCountry) {
            foreach ($feeRanges as $feeRange) {
                if (!array_key_exists(self::COUNTRY, $feeRange)) {
                    $feeRange[self::COUNTRY] = 'other';
                }

                if (
                    $feeRange[self::COUNTRY] === $customerCountry
                    && $feeRange[self::INDEX_WORKFLOW_TYPE] == $this->workflowHelper->getOptionKey($workflowType)
                    && $this->isFeeInRange($total, $feeRange)
                ) {
                    $configFee = $feeRange[self::INDEX_AMOUNT];

                    if (str_ends_with($configFee, '%')) {
                        $percentage = (float) $configFee;

                        return $total * ($percentage / 100);
                    }

                    return (float) $configFee;
                }
            }
        }

        return 0;
    }

    public function isFeeInRange(float $total, array $range): bool
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
