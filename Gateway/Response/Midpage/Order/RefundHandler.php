<?php

namespace Billink\Billink\Gateway\Response\Midpage\Order;

use Billink\Billink\Gateway\Helper\SessionReader;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class RefundHandler implements HandlerInterface
{
    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly SessionReader $sessionReader
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        /** @var Order $order */
        $order = $this->subjectReader->readOrder($handlingSubject);
        $message = 'Refund was created in Billink system. ';
        $response = $this->sessionReader->getResponse($response);
        if (isset($response['statuses'][0]['message'])) {
            $order->addCommentToStatusHistory('Message: ' . $response['statuses'][0]['message']);
        }
        $order->addCommentToStatusHistory($message);
    }
}
