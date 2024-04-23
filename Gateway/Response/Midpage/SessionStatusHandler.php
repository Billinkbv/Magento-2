<?php

namespace Billink\Billink\Gateway\Response\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Billink\Billink\Gateway\Validator\Midpage\SessionStatus;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;

class SessionStatusHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    public function __construct(
        protected readonly SessionReader $sessionReader,
        protected readonly CommandPoolInterface $commandPool,
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $response = $this->sessionReader->getResponse($response);
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        if (isset($response[SessionStatus::STATUS])) {
            if ($response[SessionStatus::STATUS] === SessionStatus::STATUS_EXPIRED) {
                // Session Expired - cancel order.
                $command = $this->commandPool->get('order_cancel');
                $command->execute($handlingSubject);
                return;
            }
            if ($response[SessionStatus::STATUS] === SessionStatus::STATUS_PAID) {
                $command = $this->commandPool->get('order_update');
                $command->execute($handlingSubject);
            }
        }
    }
}
