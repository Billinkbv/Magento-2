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
    protected OrderStatusHistoryRepositoryInterface $historyRepository;

    protected OrderStatusHistoryInterfaceFactory $historyObjectFactory;

    protected LoggerInterface $logger;

    protected array $orderMessages = [];

    protected DataObjectFactory $dataObjectFactory;

    public function __construct(
        OrderStatusHistoryRepositoryInterface $historyRepository,
        OrderStatusHistoryInterfaceFactory $historyInterfaceFactory,
        DataObjectFactory $dataObjectFactory,
        LoggerInterface $logger
    ) {
        $this->historyRepository = $historyRepository;
        $this->historyObjectFactory = $historyInterfaceFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->logger = $logger;
    }

    public function addOrderComment(OrderInterface $order, string $message = ''): void
    {
        try {
            $historyItem = $this->historyObjectFactory->create(['data' => [
                OrderStatusHistoryInterface::COMMENT => $message,
                OrderStatusHistoryInterface::STATUS => $order->getStatus(),
                OrderStatusHistoryInterface::PARENT_ID => $order->getId(),
                OrderStatusHistoryInterface::ENTITY_NAME => 'order',
                OrderStatusHistoryInterface::IS_CUSTOMER_NOTIFIED => false,
            ]]);
            $this->historyRepository->save($historyItem);
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
