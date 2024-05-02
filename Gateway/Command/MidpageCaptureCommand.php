<?php
namespace Billink\Billink\Gateway\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class MidpageCaptureCommand implements CommandInterface
{
    public function __construct(
        protected readonly LoggerInterface $logger
    ) {
    }

    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        try {
            // It's not like we need to validate or do anything at this point, but this command is needed to complete
            // magento flow of creating all linked data.
            $order = $payment->getOrder();
            // Order status update could go here.
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new LocalizedException(__("There was an error during your request."));
        }
    }
}
