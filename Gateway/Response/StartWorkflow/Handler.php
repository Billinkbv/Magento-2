<?php

namespace Billink\Billink\Gateway\Response\StartWorkflow;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Handler
 * @package Billink\Billink\Gateway\Response\StartWorkflow
 */
class Handler implements HandlerInterface
{
    const INDEX_STATUS = 'STATUSES';

    const INDEX_INVOICE_NUMBER = 'INVOICENUMBER';
    const INDEX_CODE = 'CODE';
    const INDEX_MESSAGE = 'MESSAGE';

    const RESULT_SUCCESS = '500';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Handler constructor.
     *
     * @param SubjectReader $subjectReader
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws InvalidResponseException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $responseData = $response['result']->getMsg();

        if (!isset($responseData[self::INDEX_STATUS]) || !is_array($responseData[self::INDEX_STATUS])) {
            throw new InvalidResponseException('Invalid StartWorkflow response data');
        }

        foreach ($responseData[self::INDEX_STATUS] as $item) {
            if (!isset($item[self::INDEX_CODE])) {
                throw new InvalidResponseException('Invalid StartWorkflow item code');
            }

            switch ($item[self::INDEX_CODE]) {
                case self::RESULT_SUCCESS:
                    $this->messageManager->addSuccessMessage(__('The Billink workflow for order %1 has started',
                        $item[self::INDEX_INVOICE_NUMBER]));
                    break;
                default:
                    $this->messageManager->addErrorMessage(__('The start of the Billink workflow failed.' .
                        ' Log in via the Billink portal to start the workflow from there.'));

                    $this->logger->error('Error in starting workflow. Code: '
                        . $item[self::INDEX_CODE] . ' ; Message: ' . $item[self::INDEX_MESSAGE]);
                    break;
            }
        }

    }
}