<?php
namespace Billink\Billink\Model\Payment;

use Magento\Framework\Session\SessionManager;
use Magento\Sales\Api\Data\OrderInterface;

class Session extends SessionManager
{
    public function activatePaymentSession(OrderInterface $order): self
    {
        $this->setMidpageSessionActive(true);
        $this->setSessionOrderId($order->getId());
        return $this;
    }

    public function deactivatePaymentSession(): self
    {
        $this->setMidpageSessionActive(false);
        $this->setSessionOrderId(null);
        return $this;
    }

    public function getIsActivePaymentSession(): ?bool
    {
        return $this->getMidpageSessionActive();
    }

    public function getPaymentSessionOrderId(): ?string
    {
        return $this->getSessionOrderId();
    }

    /**
     * Deactivate only in case session order id is equal to the provided order id
     */
    public function deactivatePaymentSessionById(int $entityId): void
    {
        $currentSessionId = (int)$this->getPaymentSessionOrderId();
        if ($currentSessionId === $entityId) {
            $this->deactivatePaymentSession();
        }
    }
}
