<?php

namespace Billink\Billink\Gateway\Converter;

use Billink\Billink\Gateway\Helper\Xml;
use Billink\Billink\Model\Billink\Response\ResponseFactory;
use Exception;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Psr\Log\LoggerInterface;

class ResponseConverter implements ConverterInterface
{
    /**
     * ResponseConverter constructor.
     */
    public function __construct(
        private readonly Xml $xmlHelper,
        private readonly ResponseFactory $responseFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Converts gateway response to ENV structure
     *
     * @param string $data
     */
    public function convert($data): array
    {
        $response = $this->responseFactory->create();

        try {
            $parsedResponse = $this->xmlHelper->parse($data);
            $result = $response->setData($parsedResponse);
        } catch (Exception $e) {
            $result = false;
            $this->logger->error(
                'Could not convert Gateway Response. Error was: ' . $e->getMessage() . ' ; Response was: ' . $data
            );
        }

        return ['result' => $result];
    }
}
