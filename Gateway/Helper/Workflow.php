<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Config\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Workflow
 * @package Billink\Billink\Gateway\Helper
 */
class Workflow
{
    const WORKFLOW_TYPE_PREFIX = 'workflow_';

    const TYPE_PRIVATE = 'P';
    const TYPE_BUSINESS = 'B';

    const FIELD_TYPE = 'type';
    const FIELD_NUMBER = 'number';
    const FIELD_MAX_AMOUNT = 'max_amount';
    const FIELD_CHECK = 'is_with_check';

    /**
     * @var Config
     */
    private $config;

    /**
     * Workflow constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getTypes()
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

    /**
     * @param int $storeId
     * @return ?string
     */
    public function getUsedWorkflows($storeId = null)
    {
        return $this->config->getUsedWorkflow($storeId);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getOptionKey($type)
    {
        return self::WORKFLOW_TYPE_PREFIX . $type;
    }

    /**
     * @param string $type
     * @return mixed
     * @throws LocalizedException
     */
    public function getData($type)
    {
        $workflow = $this->config->getWorkflow();
        $key = $this->getOptionKey($type);

        if (!isset($workflow[$key])) {
            throw new LocalizedException(__('Please contact your system administrator with a code 5001'));
        }

        return $workflow[$key];
    }

    /**
     * @param string $type
     * @return mixed
     * @throws LocalizedException
     */
    public function getNumber($type)
    {
        $workflow = $this->getData($type);

        if (!isset($workflow[self::FIELD_NUMBER])) {
            throw new LocalizedException(__('Please contact your system administrator with a code 5002'));
        }

        return $workflow[self::FIELD_NUMBER];
    }

    /**
     * @param string $type
     * @return bool
     * @throws LocalizedException
     */
    public function getIsWithCheck($type)
    {
        $workflow = $this->getData($type);

        if (!isset($workflow[self::FIELD_CHECK])) {
            throw new LocalizedException(__('Please contact your system administrator with a code 5003'));
        }

        return (bool)$workflow[self::FIELD_CHECK];
    }

    /**
     * @param string $type
     * @return float
     * @throws LocalizedException
     */
    public function getMaxAmount($type)
    {
        $workflow = $this->getData($type);

        if (!isset($workflow[self::FIELD_MAX_AMOUNT])) {
            throw new LocalizedException(__('Please contact your system administrator with a code 5004'));
        }

        return (float)$workflow[self::FIELD_MAX_AMOUNT];
    }
}
