<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Config\BasePaymentConfig;

/**
 * Class Gateway
 * @package Billink\Billink\Gateway\Helper
 */
class Gateway
{
    const GATEWAY_URL = 'https://client.billink.nl/api/';
    const GATEWAY_URL_DEBUG = 'https://test.billink.nl/api/';

    const CHECKUUID = 'billink_checkuuid';

    const SERVICE_CHECK = 'check';
    const SERVICE_ORDER = 'order';
    const SERVICE_START_WORKFLOW = 'start-workflow';
    const SERVICE_CREDIT = 'credit';

    private BasePaymentConfig $config;

    /**
     * Gateway constructor.
     * @param BasePaymentConfig $config
     */
    public function __construct(
        BasePaymentConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * @param string $service
     * @return string
     */
    public function getUrl($service = '')
    {
        if ($this->config->isDebugMode()) {
            return self::GATEWAY_URL_DEBUG . $service;
        }

        return self::GATEWAY_URL . $service;
    }
}
