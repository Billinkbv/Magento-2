<?php

namespace Billink\Billink\Plugin;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class InvoiceEmailSenderPlugin
 * @package Billink\Billink\Plugin
 */
class InvoiceEmailSenderPlugin
{
    /**
     * @var \Billink\Billink\Gateway\Config\Config
     */
    private $config;

    /**
     * InvoiceEmailSenderPlugin constructor.
     * @param \Billink\Billink\Gateway\Config\Config $config
     */
    public function __construct(
        \Billink\Billink\Gateway\Config\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param InvoiceSender $subject
     * @param callable $proceed
     * @param Invoice $invoice
     * @param bool $forceSyncMode
     * @return bool
     */
    public function aroundSend(
        InvoiceSender $subject,
        callable $proceed,
        Invoice $invoice,
        $forceSyncMode = false
    ) {
        if ($this->canSendEmail($invoice)) {
            return $proceed($invoice, $forceSyncMode);
        }
        $invoice->setEmailSent(true);
        return true;
    }

    /**
     * @param Invoice $invoice
     * @return bool
     */
    private function canSendEmail(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if (!$order instanceof OrderInterface) {
            return true;
        }
        $payment = $order->getPayment();
        if (!$payment instanceof OrderPaymentInterface) {
            return true;
        }
        if ($payment->getMethod() != 'billink') {
            return true;
        }
        if ($this->config
            ->getIsInvoiceEmailEnabled($invoice->getStore()->getId())) {
            return true;
        }
        return false;
    }
}