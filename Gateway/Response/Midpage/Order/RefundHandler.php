<?php

namespace Billink\Billink\Gateway\Response\Midpage\Order;

use Billink\Billink\Gateway\Helper\SessionReader;
use Billink\Billink\Gateway\Validator\Midpage\SessionCreate;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment\Transaction;

class RefundHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    public function __construct(
        private readonly \Billink\Billink\Gateway\Helper\SubjectReader $subjectReader,
        protected readonly SessionReader $sessionReader
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->subjectReader->readOrder($handlingSubject);
        $message = 'Refund was created in Billink system. ';
        $response = $this->sessionReader->getResponse($response);
        if (isset($response['statuses'][0]['message'])) {
            $order->addCommentToStatusHistory('Message: ' . $response['statuses'][0]['message']);
        }
        $order->addCommentToStatusHistory($message);
    }
}
