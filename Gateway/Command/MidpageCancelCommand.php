<?php
namespace Billink\Billink\Gateway\Command;

use Billink\Billink\Model\Payment\MidpageCancelService;
use Billink\Billink\Model\Payment\OrderHistory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class MidpageCancelCommand implements CommandInterface
{
    public function __construct(
        protected readonly CheckoutSession $session,
        protected readonly LoggerInterface $logger,
        protected readonly OrderHistory $orderHistory,
        protected readonly MidpageCancelService $cancelService
    ) {
    }

    /**
     * @param array $commandSubject
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $commandSubject): void
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
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
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->cancelService->restoreQuote();
            throw new LocalizedException(__("There was an error during your request."));
        }
        $this->session->clearHelperData();
        $this->orderHistory->processOrderMessages();
    }
}
