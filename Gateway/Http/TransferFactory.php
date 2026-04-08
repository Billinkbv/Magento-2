<?php

namespace Billink\Billink\Gateway\Http;

use Billink\Billink\Gateway\Helper\Gateway as GatewayHelper;
use Billink\Billink\Gateway\Helper\Xml as XmlHelper;
use Billink\Billink\Gateway\Request\ActionDataBuilder;
use Exception;
use Laminas\Http\Request;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransferFactory implements TransferFactoryInterface
{
    public const XML_ROOT = 'API';

    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly XmlHelper $xmlHelper,
        private readonly GatewayHelper $gatewayHelper
    ) {
    }

    /**
     * Builds gateway transfer object
     * @throws Exception
     */
    public function create(array $request): TransferInterface
    {
        $service = $request[ActionDataBuilder::SERVICE];
        unset($request[ActionDataBuilder::SERVICE]);

        $body = $this->xmlHelper->convert($request, self::XML_ROOT);

        return $this->transferBuilder
            ->setHeaders(['Content-Type' => 'text/xml'])
            ->setBody($body)
            ->setUri($this->gatewayHelper->getUrl($service))
            ->setMethod(Request::METHOD_POST)
            ->build();
    }
}
