<?php
namespace Billink\Billink\Controller\Midpage;

use Billink\Billink\Gateway\Helper\TransactionManager;
use Billink\Billink\Model\Payment\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAction implements HttpGetActionInterface
{
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly CheckoutSession $checkoutSession,
        protected readonly RedirectFactory $redirectFactory,
        protected readonly Session $paymentSession,
        protected readonly TransactionManager $transactionManager,
        protected readonly CommandPoolInterface $commandPool,
        protected readonly PaymentDataObjectFactory $paymentDataObjectFactory,
        protected readonly ManagerInterface $messageManager,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly LoggerInterface $logger,
    ) {
    }

    protected function getOrderIdFromTransaction(): ?string
    {
        // Check if transaction id is provided.
        $transactionId = $this->request->getParam('txn');
        if (!$transactionId) {
            return null;
        }
        return $this->transactionManager->validateTransaction($transactionId);
    }

    protected function loadOrderByIncrementId(string $transactionOrder): OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $transactionOrder)
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        $order = array_shift($orders);
        if (!$order) {
            throw new LocalizedException(__('Incorrect order number provided: %1. Please reach out to support.', $transactionOrder));
        }
        return $order;
    }
}
