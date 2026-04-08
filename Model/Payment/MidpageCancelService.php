<?php

namespace Billink\Billink\Model\Payment;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Psr\Log\LoggerInterface;

use function __;

class MidpageCancelService
{
    public function __construct(
        private readonly OrderRepositoryInterfaceFactory $orderRepositoryFactory,
        private readonly LoggerInterface $logger,
        private readonly CheckoutSession $session
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
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(__('There was an error during request. Please contact support'));
        }
    }

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
