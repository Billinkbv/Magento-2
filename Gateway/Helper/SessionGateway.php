<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Config\MidpageConfig;

/**
 * Class Gateway
 * @package Billink\Billink\Gateway\Helper
 */
class SessionGateway
{
    const GATEWAY_URL = 'https://session.billink.nl/api/';
    const GATEWAY_URL_DEBUG = 'https://session-staging.billink.nl/api/';

    const SERVICE_CREATE = 'v1/create';

    private MidpageConfig $config;

    /**
     * Gateway constructor.
     * @param MidpageConfig $config
     */
    public function __construct(
        MidpageConfig $config
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
