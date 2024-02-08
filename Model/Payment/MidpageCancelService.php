<?php
namespace Billink\Billink\Model\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class MidpageCancelService
{
    private OrderRepositoryInterfaceFactory $orderRepositoryFactory;
    private LoggerInterface $logger;
    private CheckoutSession $session;

    public function __construct(
        OrderRepositoryInterfaceFactory $orderRepositoryFactory,
        LoggerInterface $logger,
        CheckoutSession $session
    ) {
        $this->orderRepositoryFactory= $orderRepositoryFactory;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @param OrderInterface $order
     * @throws LocalizedException
     */
    public function cancelOrder(OrderInterface $order): void
    {
        $repository = $this->getRepository();
        $order = $repository->get($order->getId());
        $exception = null;
        try {
            if ($order->canCancel()) {
                $order->cancel();
                $repository->save($order);
                $this->restoreQuote();
                if ($exception !== null) {
                    throw $exception;
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(__('There was an error during request. Please contact support'));
        }
    }

    /**
     * restore checkout quote
     * @return void
     */
    public function restoreQuote(): void
    {
        $this->session->restoreQuote();
    }

    /**
     * create new repository object each time
     * to be sure order object returned
     * is clear from changes related to failed authorization
     * or invoice, registry[] object caching issue
     */
    protected function getRepository(): OrderRepositoryInterface
    {
        return $this->orderRepositoryFactory->create();
    }
}
