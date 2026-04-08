<?php

namespace Billink\Billink\Gateway\Command;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

use function __;

class MidpageCaptureCommand implements CommandInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function execute(array $commandSubject): void
    {
        /** @var Payment $payment */
        $payment = SubjectReader::readPayment($commandSubject)->getPayment();
        ContextHelper::assertOrderPayment($payment);
        try {
            // It's not like we need to validate or do anything at this point, but this command is needed to complete
            // magento flow of creating all linked data.
            $order = $payment->getOrder();
            // Order status update could go here.
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new LocalizedException(__('There was an error during your request.'));
        }
    }
}
