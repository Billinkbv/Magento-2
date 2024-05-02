<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;

class ClientDataBuilder implements BuilderInterface
{
    public const VERSION = 'VERSION';
    public const CLIENTUSERNAME = 'CLIENTUSERNAME';
    public const CLIENTID = 'CLIENTID';

    public function __construct(
        private readonly MidpageConfig $config
    ) {
    }

    /**
     * Builds ENV request
     */
    public function build(array $buildSubject): array
    {
        return [
            self::VERSION => $this->config->getApiVersion(),
            self::CLIENTUSERNAME => $this->config->getAccountName(),
            self::CLIENTID => $this->config->getAccountId()
        ];
    }
}
