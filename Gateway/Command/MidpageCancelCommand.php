<?php

namespace Billink\Billink\Gateway\Command;

use Billink\Billink\Model\Payment\MidpageCancelService;
use Billink\Billink\Model\Payment\OrderHistory;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

use function __;

class MidpageCancelCommand implements CommandInterface
{
    public function __construct(
        private readonly CheckoutSession $session,
        private readonly LoggerInterface $logger,
        private readonly OrderHistory $orderHistory,
        private readonly MidpageCancelService $cancelService
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
            $this->cancelService->cancelOrder($payment->getOrder());
            $this->orderHistory->setOrderMessage(
                $payment->getOrder(),
                __('Order has been cancelled by customer.')
            );
        } catch (LocalizedException $e) {
            $this->cancelService->restoreQuote();
            throw $e;
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->cancelService->restoreQuote();
            throw new LocalizedException(__('There was an error during your request.'));
        }
        $this->session->clearHelperData();
        $this->orderHistory->processOrderMessages();
    }
}
