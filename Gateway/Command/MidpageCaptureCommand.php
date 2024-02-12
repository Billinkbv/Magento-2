<?php
namespace Billink\Billink\Gateway\Command;

use Billink\Billink\Model\Payment\OrderHistory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class MidpageCaptureCommand implements CommandInterface
{
    private OrderRepositoryInterface $orderRepository;
    private LoggerInterface $logger;
    private OrderHistory $orderHistory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        OrderHistory $orderHistory
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->orderHistory = $orderHistory;
    }

    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        try {
            $order = $payment->getOrder();
            $invoiceCollection = $order->getInvoiceCollection();
            $invoice = $invoiceCollection->getFirstItem();
            // Well, basically we make sure that invoice exists here and that's all.
        } catch (\Exception $e) {
            throw new LocalizedException(__("There was an error during your request."));
        }
    }
}
