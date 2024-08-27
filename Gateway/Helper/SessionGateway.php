<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Config\MidpageConfig;

/**
 * Class Gateway
 * @package Billink\Billink\Gateway\Helper
 */
class SessionGateway
{
    public const GATEWAY_URL = 'https://api.billink.nl/';
    public const GATEWAY_URL_DEBUG = 'https://api-staging.billink.nl/';

    public const SERVICE_CREATE = 'v2/session/create';
    public const SERVICE_STATUS = 'v2/session/status';
    public const SERVICE_WEBSHOP_SETTINGS = 'v2/client/webshop-settings';
    public const SERVICE_INVOICE_CREDIT = 'v2/client/invoice/credit';

    public function __construct(
        protected readonly MidpageConfig $config
    ) {
    }

    public function getUrl(string $service = ''): string
    {
        if ($this->config->isTestMode()) {
            return self::GATEWAY_URL_DEBUG . $service;
        }

        return self::GATEWAY_URL . $service;
    }
}
