<?php

namespace Billink\Billink\Gateway\Converter;

use Billink\Billink\Gateway\Helper\Xml;
use Billink\Billink\Model\Billink\Response\ResponseFactory;
use Psr\Log\LoggerInterface;

class ResponseConverter implements \Magento\Payment\Gateway\Http\ConverterInterface
{
    /**
     * @var Xml
     */
    private $xmlHelper;

    /**
     * @var \Billink\Billink\Model\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ResponseConverter constructor.
     * @param Xml $xmlHelper
     * @param ResponseFactory $responseFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Xml $xmlHelper,
        ResponseFactory $responseFactory,
        LoggerInterface $logger
    ) {
        $this->xmlHelper = $xmlHelper;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * Converts gateway response to ENV structure
     *
     * @param string $data
     * @return array
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function convert($data)
    {
        $response = $this->responseFactory->create();

        try {
            $parsedResponse = $this->xmlHelper->parse($data);
            $result = $response->setData($parsedResponse);
        } catch (\Exception $e) {
            $result = false;
            $this->logger->error('Could not convert Gateway Response. Error was: ' . $e->getMessage()
                . ' ; Response was: ' . $data);
        }

        return ['result' => $result];
    }
}
