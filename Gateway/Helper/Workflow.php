<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Config\Config;
use Magento\Framework\Exception\LocalizedException;

class Workflow
{
    public const WORKFLOW_TYPE_PREFIX = 'workflow_';

    public const TYPE_PRIVATE = 'P';
    public const TYPE_BUSINESS = 'B';

    public const FIELD_TYPE = 'type';
    public const FIELD_NUMBER = 'number';
    public const FIELD_MAX_AMOUNT = 'max_amount';
    public const FIELD_CHECK = 'is_with_check';

    public function __construct(
        private readonly Config $config
    ) {
    }

    public function getTypes(): array
    {
        return [
            [
                'label' => __('Private'),
                'value' => self::TYPE_PRIVATE
            ],
            [
                'label' => __('Business'),
                'value' => self::TYPE_BUSINESS
            ]
        ];
    }

    public function getUsedWorkflows(?int $storeId = null): ?string
    {
        return $this->config->getUsedWorkflow($storeId);
    }

    public function getOptionKey(string $type): string
    {
        return self::WORKFLOW_TYPE_PREFIX . $type;
    }

    /**
     * @throws LocalizedException
     */
    public function getData(string $type): mixed
    {
        $workflow = $this->config->getWorkflow();
        $key = $this->getOptionKey($type);

        if (!isset($workflow[$key])) {
            throw new LocalizedException(__('Please contact your system administrator with a code 5001'));
        }

        return $workflow[$key];
    }

    /**
     * @throws LocalizedException
     */
    public function getNumber(string $type): mixed
    {
        $workflow = $this->getData($type);

        if (!isset($workflow[self::FIELD_NUMBER])) {
            throw new LocalizedException(__('Please contact your system administrator with a code 5002'));
        }

        return $workflow[self::FIELD_NUMBER];
    }

    /**
     * @throws LocalizedException
     */
    public function getIsWithCheck(string $type): bool
    {
        $workflow = $this->getData($type);

        if (!isset($workflow[self::FIELD_CHECK])) {
            throw new LocalizedException(__('Please contact your system administrator with a code 5003'));
        }

        return (bool) $workflow[self::FIELD_CHECK];
    }

    /**
     * @throws LocalizedException
     */
    public function getMaxAmount(string $type): float
    {
        $workflow = $this->getData($type);

        if (!isset($workflow[self::FIELD_MAX_AMOUNT])) {
            throw new LocalizedException(__('Please contact your system administrator with a code 5004'));
        }

        return (float) $workflow[self::FIELD_MAX_AMOUNT];
    }
}
