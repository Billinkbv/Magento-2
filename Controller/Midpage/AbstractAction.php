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
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAction implements HttpGetActionInterface
{
    protected RequestInterface $request;
    protected CheckoutSession $checkoutSession;
    protected RedirectFactory $redirectFactory;
    protected Session $paymentSession;
    protected TransactionManager $transactionManager;
    protected CommandPoolInterface $commandPool;
    protected PaymentDataObjectFactory $paymentDataObjectFactory;
    protected ManagerInterface $messageManager;
    protected OrderRepositoryInterface $orderRepository;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    protected LoggerInterface $logger;

    public function __construct(
        RequestInterface $request,
        CheckoutSession $checkoutSession,
        RedirectFactory $redirectFactory,
        Session $paymentSession,
        TransactionManager $transactionManager,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        ManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger,
    ) {
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->paymentSession = $paymentSession;
        $this->transactionManager = $transactionManager;
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    protected function getOrderFromTransaction(): ?string
    {
        // Check if transaction id is provided.
        $transactionId = $this->request->getParam('txn');
        if (!$transactionId) {
            return null;
        }
        $orderId = $this->transactionManager->validateTransaction($transactionId);
        if (!$orderId) {
            return null;
        }
        return $orderId;
    }

    protected function loadOrderByIncrementId(string $transactionOrder)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $transactionOrder)
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        $order = array_shift($orders);
        if (!$order) {
            throw new LocalizedException(__('Incorrect order number provided: %1. Please reach for the support.', $transactionOrder));
        }
        return $order;
    }
}
