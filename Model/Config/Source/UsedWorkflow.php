<?php
namespace Billink\Billink\Model\Config\Source;

class UsedWorkflow implements \Magento\Framework\Data\OptionSourceInterface
{
    const CONFIG_WORKFLOW_PRIVATE = 'workflow_P';
    const CONFIG_WORKFLOW_BUSINESS = 'workflow_B';
    const CONFIG_WORKFLOW_ALL = 'workflow_all';

    /**
     * @inheirtDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CONFIG_WORKFLOW_PRIVATE,
                'label' => __('Private only')
            ], [
                'value' => self::CONFIG_WORKFLOW_BUSINESS,
                'label' => __('Business only')
            ], [
                'value' => self::CONFIG_WORKFLOW_ALL,
                'label' => __('Both private and business')
            ]
        ];
    }
}
