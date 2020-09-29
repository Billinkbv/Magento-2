<?php

namespace Billink\Billink\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class ActionDataBuilder implements BuilderInterface
{
    const ACTION = 'ACTION';
    const SERVICE = 'SERVICE';

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $service;

    /**
     * ActionDataBuilder constructor.
     * @param string $action
     * @param string $service
     */
    public function __construct($action, $service)
    {
        $this->action = $action;
        $this->service = $service;
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
            self::ACTION => $this->action,
            self::SERVICE => $this->service
        ];

        return $result;
    }

}