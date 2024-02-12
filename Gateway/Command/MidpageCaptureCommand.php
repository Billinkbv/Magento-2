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
        } catch (LocalizedException $e) {
            // Probably send invoice email here?
        } catch (\Exception $e) {
            throw new LocalizedException(__("There was an error during your request."));
        }
    }
}
