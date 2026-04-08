<?php

namespace Billink\Billink\Plugin;

use Billink\Billink\Gateway\Config\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;

class InvoiceEmailSenderPlugin
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    public function aroundSend(
        InvoiceSender $subject,
        callable $proceed,
        Invoice $invoice,
        bool $forceSyncMode = false
    ): bool {
        if ($this->canSendEmail($invoice)) {
            return $proceed($invoice, $forceSyncMode);
        }
        $invoice->setEmailSent(true);

        return true;
    }

    private function canSendEmail(Invoice $invoice): bool
    {
        $order = $invoice->getOrder();
        if (!$order instanceof OrderInterface) {
            return true;
        }
        $payment = $order->getPayment();

        return !$payment instanceof OrderPaymentInterface
            || $payment->getMethod() !== 'billink'
            || $this->config->getIsInvoiceEmailEnabled($invoice->getStore()->getId());
    }
}
