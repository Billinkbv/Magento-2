<?php

namespace Billink\Billink\Cron;

use Billink\Billink\Model\Ui\ConfigProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class OrderStatus
{
    public function __construct(
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly CommandPoolInterface $commandPool,
        protected readonly PaymentDataObjectFactory $paymentDataObjectFactory,
        protected readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        // Select All pending order between 10 minutes and 3 hours.
        $dateMin = new \Magento\Framework\DB\Sql\Expression('DATE_SUB(NOW(), INTERVAL 3 HOUR)');
        $dateMax = new \Magento\Framework\DB\Sql\Expression('DATE_SUB(NOW(), INTERVAL 10 MINUTE)');
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::STATUS, 'pending')
            ->addFilter(OrderInterface::CREATED_AT, $dateMin, 'gt')
            ->addFilter(OrderInterface::CREATED_AT, $dateMax, 'lt')
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        /** @var OrderInterface $order */
        foreach ($orders as $order) {
            try {
                $payment = $order->getPayment();
                // Ignore orders without payment or non-midpage ones
                if (!$payment || $payment->getMethod() !== ConfigProvider::CODE_MIDPAGE) {
                    continue;
                }
                /** @var PaymentDataObjectInterface $paymentDO */
                $paymentDO = [
                    'payment' => $this->paymentDataObjectFactory->create($payment)
                ];
                $command = $this->commandPool->get('order_status');
                $command->execute($paymentDO);
            } catch (LocalizedException $e) {
                $this->logger->error(
                    'Failed to process pending order: '. $e->getMessage(),
                    ['order_id' => $order->getIncrementId(), 'id' => $order->getEntityId(), 'trace' => $e->getTraceAsString()]
                );
            }
        }
    }
}
