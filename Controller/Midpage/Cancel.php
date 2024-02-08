<?php
namespace Billink\Billink\Controller\Midpage;

use Billink\Billink\Model\Payment\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Cancel implements HttpGetActionInterface
{
    private CheckoutSession $checkoutSession;
    private RedirectFactory $redirectFactory;
    private Session $paymentSession;
    private OrderRepositoryInterface $orderRepository;
    private ManagerInterface $messageManager;
    private LoggerInterface $logger;
    private PaymentDataObjectFactory $paymentDataObjectFactory;
    private CommandPoolInterface $commandPool;

    public function __construct(
        CheckoutSession $checkoutSession,
        RedirectFactory $redirectFactory,
        Session $paymentSession,
        OrderRepositoryInterface $orderRepository,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->paymentSession = $paymentSession;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->commandPool = $commandPool;
    }

    /**
     * @return
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        $params = [
            '_secure'   => true,
        ];
        if (!$lastOrderId) {
            return $resultRedirect->setPath('checkout/cart', $params);
        }
        try {
            $order = $this->checkoutSession->getLastRealOrder();

            $this->paymentSession->deactivatePaymentSession();
            /** @var PaymentDataObjectInterface $paymentDO */
            $paymentDO = [
                'payment' => $this->paymentDataObjectFactory->create($order->getPayment())
            ];
            $command = $this->commandPool->get('order_cancel');
            $command->execute($paymentDO);

            // Move order to hidden cancelled status
            //$order->setStatus('canceled');
            //$this->orderRepository->save($order);
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was an error during your request.'));
            $this->logger->critical($e);
        }
        return $resultRedirect->setPath('checkout/cart', $params);
    }
}
