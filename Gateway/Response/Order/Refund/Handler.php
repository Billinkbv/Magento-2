<?php

namespace Billink\Billink\Gateway\Response\Order\Refund;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Validator\OrderDataValidator;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class Handler
 *
 * @package Billink\Billink\Gateway\Response\Order\Refund
 */
class Handler implements HandlerInterface
{
    public function __construct(
        private readonly SubjectReader $subjectReader,
    ) {
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (isset($handlingSubject[OrderDataValidator::INDEX_FLAG_VALIDATION])) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->subjectReader->readOrder($handlingSubject);
        $order->addCommentToStatusHistory('Refund was created in Billink system.');

        $payment = $order->getPayment();
        // +1 because the creditmemo is not created yet
        $idx = $order->getCreditmemosCollection()->getSize() + 1;
        $payment->setLastTransId('billink-refund-' . $order->getIncrementId() . '-' . $idx);
    }
}
