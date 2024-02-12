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
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Place implements HttpGetActionInterface
{
    private RequestInterface $request;
    private CheckoutSession $checkoutSession;
    private RedirectFactory $redirectFactory;
    private Session $paymentSession;
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private CommandPoolInterface $commandPool;
    private PaymentDataObjectFactory $paymentDataObjectFactory;
    private TransactionManager $transactionManager;
    private ManagerInterface $messageManager;
    private LoggerInterface $logger;

    public function __construct(
        RequestInterface $request,
        CheckoutSession $checkoutSession,
        RedirectFactory $redirectFactory,
        Session $paymentSession,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        TransactionManager $transactionManager,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->paymentSession = $paymentSession;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->transactionManager = $transactionManager;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();

        $transationOrder = $this->getOrderFromTransaction();
        if (!$transationOrder) {
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;
        }

        $params = ['_secure' => true];
        $order = $this->checkoutSession->getLastRealOrder();
        try {
            if ($order->getIncrementId() === $transationOrder) {
                // Normal flow - customer returns on alive session
                $this->paymentSession->deactivatePaymentSession();
                $this->executeOrderUpdate($order);
            } else {
                // Updated flow - session is dead, but the order transaction id is correct one.
                // Should close checkout session, add loaded order there and redirect customer
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter('increment_id', $transationOrder)
                    ->create();
                $orders = $this->orderRepository->getList($searchCriteria)->getItems();
                if (!$orders || !isset($orders[0])) {

                }
                $order = $orders[0];
                $this->paymentSession->deactivatePaymentSessionById($order->getEntityId());
                $this->executeOrderUpdate($order);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e);
            $resultRedirect->setPath('checkout', $params);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was an error during your request.'));
            $this->logger->critical($e);
            $resultRedirect->setPath('checkout', $params);
            return $resultRedirect;
        }
        $resultRedirect->setPath('checkout/onepage/success', $params);
        return $resultRedirect;
    }

    private function getOrderFromTransaction(): ?string
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

    private function executeOrderUpdate(\Magento\Sales\Model\Order $order)
    {
        $command = $this->commandPool->get('order_update');
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = [
            'payment' => $this->paymentDataObjectFactory->create($order->getPayment())
        ];
        $command->execute($paymentDO);
    }
}
