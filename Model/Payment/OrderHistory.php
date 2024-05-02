<?php
namespace Billink\Billink\Model\Payment;

use Magento\Framework\DataObjectFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Psr\Log\LoggerInterface;

class OrderHistory
{
    protected array $orderMessages = [];

    public function __construct(
        protected readonly OrderStatusHistoryRepositoryInterface $historyObjectFactory,
        protected readonly OrderStatusHistoryInterfaceFactory $historyInterfaceFactory,
        protected readonly DataObjectFactory $dataObjectFactory,
        protected readonly LoggerInterface $logger
    ) {
    }

    public function addOrderComment(OrderInterface $order, string $message = ''): void
    {
        try {
            $historyItem = $this->historyInterfaceFactory->create(['data' => [
                OrderStatusHistoryInterface::COMMENT => $message,
                OrderStatusHistoryInterface::STATUS => $order->getStatus(),
                OrderStatusHistoryInterface::PARENT_ID => $order->getId(),
                OrderStatusHistoryInterface::ENTITY_NAME => 'order',
                OrderStatusHistoryInterface::IS_CUSTOMER_NOTIFIED => false,
            ]]);
            $this->historyObjectFactory->save($historyItem);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    public function setOrderMessage(OrderInterface $order, string $message): void
    {
        if ($order->getId()) {
            $data = $this->dataObjectFactory->create([
                'data' => [
                    'order' => $order,
                    'message' => $message
                ]
            ]);
            $this->orderMessages[$order->getId()][] = $data;
        }
    }

    /**
     * Check all order messages to update, process them and flush log
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
