<?php

namespace Billink\Billink\Gateway\Http;

use Billink\Billink\Gateway\Helper\SessionGateway as GatewayHelper;
use Billink\Billink\Gateway\Request\ActionDataBuilder;
use Laminas\Http\Request;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class JsonTransferFactory implements TransferFactoryInterface
{
    private TransferBuilder $transferBuilder;
    private GatewayHelper $gatewayHelper;

    public function __construct(
        TransferBuilder $transferBuilder,
        GatewayHelper $gatewayHelper
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->gatewayHelper = $gatewayHelper;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $service = $request[ActionDataBuilder::SERVICE];
        unset($request[ActionDataBuilder::SERVICE]);
        unset($request[ActionDataBuilder::ACTION]);

        $body = json_encode($request);

        return $this->transferBuilder
            ->setHeaders(['Content-Type' => 'application/json'])
            ->setBody($body)
            ->setUri($this->gatewayHelper->getUrl($service))
            ->setMethod(Request::METHOD_POST)
            ->build();
    }
}
