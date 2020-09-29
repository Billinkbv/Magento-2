<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Config\Config;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class ClientDataBuilder implements BuilderInterface
{
    const VERSION = 'VERSION';
    const CLIENTUSERNAME = 'CLIENTUSERNAME';
    const CLIENTID = 'CLIENTID';

    /**
     * @var Config
     */
    private $config;

    /**
     * ClientDataBuilder constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $result = [
            self::VERSION => $this->config->getApiVersion(),
            self::CLIENTUSERNAME => $this->config->getAccountName(),
            self::CLIENTID => $this->config->getAccountId()
        ];

        return $result;
    }
}