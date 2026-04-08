<?php

namespace Billink\Billink\Gateway\Http;

use Billink\Billink\Gateway\Helper\SessionGateway as GatewayHelper;
use Billink\Billink\Gateway\Request\ActionDataBuilder;
use Laminas\Http\Request;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

use function json_encode;

class JsonTransferFactory implements TransferFactoryInterface
{
    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly GatewayHelper $gatewayHelper
    ) {
    }

    /**
     * Builds gateway transfer object
     */
    public function create(array $request): TransferInterface
    {
        $service = $request[ActionDataBuilder::SERVICE];
        unset($request[ActionDataBuilder::SERVICE], $request[ActionDataBuilder::ACTION]);

        $body = json_encode($request);

        return $this->transferBuilder
            ->setHeaders(['Content-Type' => 'application/json'])
            ->setBody($body)
            ->setUri($this->gatewayHelper->getUrl($service))
            ->setMethod(Request::METHOD_POST)
            ->build();
    }
}
