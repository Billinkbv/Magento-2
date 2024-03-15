<?php
namespace Billink\Billink\Gateway\Command;

use Billink\Billink\Model\Payment\MidpageCancelService;
use Billink\Billink\Model\Payment\OrderHistory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class MidpageUpdateCommand implements CommandInterface
{
    public function __construct(
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly LoggerInterface $logger,
        protected readonly OrderHistory $orderHistory,
        protected readonly MidpageCancelService $cancelService
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        try {
            $order = $payment->getOrder();
            $this->authorizeOrder($payment->getOrder());
            $this->createInvoice($payment->getOrder());
        } catch (LocalizedException $e) {
            $this->logger->error($e, [
                'order' => $order ? $order->getIncrementId() : 'undefined',
                'trace' => $e->getTraceAsString()
            ]);
            $this->cancelService->cancelOrder($payment->getOrder());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e, [
                'order' => $order ? $order->getIncrementId() : 'undefined',
                'trace' => $e->getTraceAsString()
            ]);
            $this->cancelService->cancelOrder($payment->getOrder());
            throw new LocalizedException(__("There was an error during your request."));
        } finally {
            $this->orderHistory->processOrderMessages();
        }
    }

    /**
     * @param OrderInterface $order
     */
    protected function createInvoice(OrderInterface $order)
    {
        $invoice = $order->getPayment()->capture(null);
        $this->orderRepository->save($invoice->getOrder());
    }

    /**
     * @param OrderInterface $order
     */
    protected function authorizeOrder(OrderInterface $order)
    {
        $baseTotalDue = $order->getBaseTotalDue();
        $order->getPayment()->authorize(true, $baseTotalDue);
        $this->orderRepository->save($order);
    }
}
