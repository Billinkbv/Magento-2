<?php

namespace Billink\Billink\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

use function __;

class UsedWorkflow implements OptionSourceInterface
{
    public const CONFIG_WORKFLOW_PRIVATE = 'workflow_P';
    public const CONFIG_WORKFLOW_BUSINESS = 'workflow_B';
    public const CONFIG_WORKFLOW_ALL = 'workflow_all';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::CONFIG_WORKFLOW_PRIVATE,
                'label' => __('Private only')
            ],
            [
                'value' => self::CONFIG_WORKFLOW_BUSINESS,
                'label' => __('Business only')
            ],
            [
                'value' => self::CONFIG_WORKFLOW_ALL,
                'label' => __('Both private and business')
            ]
        ];
    }
}
