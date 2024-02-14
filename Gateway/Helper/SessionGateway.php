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

    public function __construct(
        protected readonly MidpageConfig $config
    ) {
    }

    public function getUrl(string $service = ''): string
    {
        if ($this->config->isDebugMode()) {
            return self::GATEWAY_URL_DEBUG . $service;
        }

        return self::GATEWAY_URL . $service;
    }
}
