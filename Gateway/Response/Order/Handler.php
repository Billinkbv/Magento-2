<?php

namespace Billink\Billink\Gateway\Response\Order;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Validator\OrderDataValidator;
use Exception;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class Handler implements HandlerInterface
{
    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly TransactionFactory $transactionFactory
    ) {
    }

    /**
     * Handles response
     *
     * @throws LocalizedException
     * @throws Exception
     */
    public function handle(array $handlingSubject, array $response): void
    {
        if (isset($handlingSubject[OrderDataValidator::INDEX_FLAG_VALIDATION])) {
            return;
        }

        /** @var Order $order */
        $order = $this->subjectReader->readOrder($handlingSubject);
        $order->addStatusHistoryComment('Order was created in Billink system.');

        if ($order->canInvoice()) {
            $txnId = 'billink-' . $order->getIncrementId();
            $order->getPayment()->setLastTransId($txnId);
            $invoice = $order->prepareInvoice()
                ->register()
                ->setTransactionId($txnId)
                ->pay();
            $this->transactionFactory->create()
                ->addObject($order)
                ->addObject($invoice)
                ->save();
            $order->addStatusHistoryComment('Invoice was automatically registered and set to paid');
        }
    }
}
