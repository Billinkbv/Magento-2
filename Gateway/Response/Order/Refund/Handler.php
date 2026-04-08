<?php

namespace Billink\Billink\Gateway\Response\Order\Refund;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Validator\OrderDataValidator;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class Handler implements HandlerInterface
{
    public function __construct(
        private readonly SubjectReader $subjectReader,
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        if (isset($handlingSubject[OrderDataValidator::INDEX_FLAG_VALIDATION])) {
            return;
        }

        /** @var Order $order */
        $order = $this->subjectReader->readOrder($handlingSubject);
        $order->addCommentToStatusHistory('Refund was created in Billink system.');

        $payment = $order->getPayment();
        // +1 because the creditmemo is not created yet
        $idx = $order->getCreditmemosCollection()->getSize() + 1;
        $payment->setLastTransId('billink-refund-' . $order->getIncrementId() . '-' . $idx);
    }
}
