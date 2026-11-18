<?php

namespace Billink\Billink\Gateway\Response\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Psr\Log\LoggerInterface;

use function __;

class StartWorkflowHandler implements HandlerInterface
{
    public const RESULT_SUCCESS = 500;

    public function __construct(
        private readonly SessionReader $sessionReader,
        private readonly ManagerInterface $messageManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $response = $this->sessionReader->getResponse($response);

        foreach ($response['statuses'] as $item) {
            if ((int) ($item['code'] ?? 0) === self::RESULT_SUCCESS) {
                $this->messageManager->addSuccessMessage(
                    __('The Billink workflow for order %1 has started', $item['invoiceNumber'] ?? '')
                );
                continue;
            }

            $this->messageManager->addErrorMessage(
                __(
                    'The start of the Billink workflow failed.' . ' Log in via the Billink portal to start the workflow from there.'
                )
            );
            $this->logger->error(
                'Error in starting workflow. Code: '
                . ($item['code'] ?? '') . ' ; Message: ' . ($item['message'] ?? '')
            );
        }
    }
}
