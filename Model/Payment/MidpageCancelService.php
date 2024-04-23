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
    public function __construct(
        protected readonly OrderRepositoryInterfaceFactory $orderRepositoryFactory,
        protected readonly LoggerInterface $logger,
        protected readonly CheckoutSession $session
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function cancelOrder(OrderInterface $order): void
    {
        $repository = $this->getRepository();
        $order = $repository->get($order->getId());
        try {
            if ($order->canCancel()) {
                $order->cancel();
                $repository->save($order);
                $this->restoreQuote();
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(__('There was an error during request. Please contact support'));
        }
    }

    /**
     * restore checkout quote
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
