<?php

namespace Billink\Billink\Gateway\Http\Client;

use Laminas\Http\Request;
use Laminas\Http\Exception\RuntimeException as LaminasRuntimeException;
use Laminas\Http\Client\Exception\RuntimeException;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class Laminas
 * @package Magento\Payment\Gateway\Http\Client
 * @api
 */
class Laminas implements ClientInterface
{
    /**
     * @var LaminasClientFactory
     */
    private $clientFactory;

    /**
     * @var ConverterInterface | null
     */
    private $converter;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param LaminasClientFactory $clientFactory
     * @param Logger $logger
     * @param ConverterInterface | null $converter
     */
    public function __construct(
        LaminasClientFactory $clientFactory,
        Logger $logger,
        ConverterInterface $converter = null
    ) {
        $this->clientFactory = $clientFactory;
        $this->converter = $converter;
        $this->logger = $logger;
    }

    /**
     * {inheritdoc}
     * @param TransferInterface $transferObject
     *
     * @return array
     *
     * @throws ClientException
     * @throws ConverterException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [
            'request' => $transferObject->getBody(),
            'request_uri' => $transferObject->getUri(),
            'method' => $transferObject->getMethod(),
        ];
        $result = [];
        /** @var LaminasClient $client */
        $client = $this->clientFactory->create();

        $client->setOptions($transferObject->getClientConfig());
        $client->setMethod($transferObject->getMethod());

        $body = $transferObject->getBody();

        switch ($transferObject->getMethod()) {
            case Request::METHOD_GET:
                $client->setParameterGet($body);
                break;
            case Request::METHOD_POST:
                // AW Modified - set RAW data instead of POST parameters
                $client->setRawBody($body);
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'Unsupported HTTP method %s',
                        $transferObject->getMethod()
                    )
                );
        }

        $client->setHeaders($transferObject->getHeaders());
        $client->setUrlEncodeBody($transferObject->shouldEncode());
        $client->setUri($transferObject->getUri());

        try {
            $response = $client->send();

            $result = $this->converter
                ? $this->converter->convert($response->getBody())
                : [$response->getBody()];
            $log['response'] = $result;
        } catch (RuntimeException|LaminasRuntimeException $e) {
            throw new ClientException(
                __($e->getMessage())
            );
        } finally {
            $this->logger->debug($log);
        }

        return $result;
    }
}
