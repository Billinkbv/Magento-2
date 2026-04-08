<?php

namespace Billink\Billink\Gateway\Command;

use Billink\Billink\Model\Payment\MidpageCancelService;
use Billink\Billink\Model\Payment\OrderHistory;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

use function __;

class MidpageUpdateCommand implements CommandInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
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
            $order = $payment->getOrder();
            $this->authorizeOrder($payment->getOrder());
            $this->createInvoice($payment->getOrder());
        } catch (LocalizedException $e) {
            $this->logger->error(
                $e,
                [
                    'order' => $order ? $order->getIncrementId() : 'undefined',
                    'trace' => $e->getTraceAsString()
                ]
            );
            $this->cancelService->cancelOrder($payment->getOrder());
            throw $e;
        } catch (Exception $e) {
            $this->logger->critical(
                $e,
                [
                    'order' => $order ? $order->getIncrementId() : 'undefined',
                    'trace' => $e->getTraceAsString()
                ]
            );
            $this->cancelService->cancelOrder($payment->getOrder());
            throw new LocalizedException(__('There was an error during your request.'));
        } finally {
            $this->orderHistory->processOrderMessages();
        }
    }

    /**
     * @throws LocalizedException
     */
    private function createInvoice(OrderInterface $order): void
    {
        $invoice = $order->getPayment()->capture(null);
        $this->orderRepository->save($invoice->getOrder());
    }

    private function authorizeOrder(OrderInterface $order): void
    {
        $baseTotalDue = $order->getBaseTotalDue();
        $order->getPayment()->authorize(true, $baseTotalDue);
        $this->orderRepository->save($order);
    }
}
