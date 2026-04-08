<?php

namespace Billink\Billink\Gateway\Response\StartWorkflow;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Psr\Log\LoggerInterface;

use function __;
use function is_array;

class Handler implements HandlerInterface
{
    public const INDEX_STATUS = 'STATUSES';

    public const INDEX_INVOICE_NUMBER = 'INVOICENUMBER';
    public const INDEX_CODE = 'CODE';
    public const INDEX_MESSAGE = 'MESSAGE';

    public const RESULT_SUCCESS = '500';

    public function __construct(
        private readonly ManagerInterface $messageManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Handles response
     *
     * @throws InvalidResponseException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $responseData = $response['result']->getMsg();

        if (!isset($responseData[self::INDEX_STATUS]) || !is_array($responseData[self::INDEX_STATUS])) {
            throw new InvalidResponseException('Invalid StartWorkflow response data');
        }

        foreach ($responseData[self::INDEX_STATUS] as $item) {
            if (!isset($item[self::INDEX_CODE])) {
                throw new InvalidResponseException('Invalid StartWorkflow item code');
            }

            if ($item[self::INDEX_CODE] == self::RESULT_SUCCESS) {
                $this->messageManager->addSuccessMessage(
                    __('The Billink workflow for order %1 has started', $item[self::INDEX_INVOICE_NUMBER])
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __(
                        'The start of the Billink workflow failed.' . ' Log in via the Billink portal to start the workflow from there.'
                    )
                );

                $this->logger->error(
                    'Error in starting workflow. Code: '
                    . $item[self::INDEX_CODE] . ' ; Message: ' . $item[self::INDEX_MESSAGE]
                );
            }
        }
    }
}
