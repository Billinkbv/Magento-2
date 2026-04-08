<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Config\Config;

class Gateway
{
    public const GATEWAY_URL = 'https://client.billink.nl/api/';
    public const GATEWAY_URL_DEBUG = 'https://test.billink.nl/api/';

    public const CHECKUUID = 'billink_checkuuid';

    public const SERVICE_CHECK = 'check';
    public const SERVICE_ORDER = 'order';
    public const SERVICE_START_WORKFLOW = 'start-workflow';
    public const SERVICE_CREDIT = 'credit';

    public function __construct(
        private readonly Config $config
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
