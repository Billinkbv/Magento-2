<?php

namespace Billink\Billink\Model\Payment;

use Exception;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Psr\Log\LoggerInterface;

class OrderHistory
{
    private array $orderMessages = [];

    public function __construct(
        private readonly OrderStatusHistoryRepositoryInterface $historyObjectFactory,
        private readonly OrderStatusHistoryInterfaceFactory $historyInterfaceFactory,
        private readonly DataObjectFactory $dataObjectFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function addOrderComment(OrderInterface $order, string $message = ''): void
    {
        try {
            $historyItem = $this->historyInterfaceFactory->create(
                [
                    'data' => [
                        OrderStatusHistoryInterface::COMMENT => $message,
                        OrderStatusHistoryInterface::STATUS => $order->getStatus(),
                        OrderStatusHistoryInterface::PARENT_ID => $order->getId(),
                        OrderStatusHistoryInterface::ENTITY_NAME => 'order',
                        OrderStatusHistoryInterface::IS_CUSTOMER_NOTIFIED => false,
                    ]
                ]
            );
            $this->historyObjectFactory->save($historyItem);
        } catch (Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    public function setOrderMessage(OrderInterface $order, string $message): void
    {
        if ($order->getId()) {
            $this->orderMessages[$order->getId()][] = $this->dataObjectFactory->create(
                [
                    'data' => [
                        'order' => $order,
                        'message' => $message
                    ]
                ]
            );
        }
    }

    /**
     * Check all order messages to update, process them, and flush log
     */
    public function processOrderMessages(): void
    {
        foreach ($this->orderMessages as $orderId => $data) {
            foreach ($data as $item) {
                $this->addOrderComment($item->getOrder(), $item->getMessage());
            }
        }
        $this->orderMessages = [];
    }
}
