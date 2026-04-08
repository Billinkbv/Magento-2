<?php

namespace Billink\Billink\Gateway\Response\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Billink\Billink\Gateway\Validator\Midpage\AbstractCommon;
use Billink\Billink\Gateway\Validator\Midpage\SessionStatus;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class SessionStatusHandler implements HandlerInterface
{
    public function __construct(
        private readonly SessionReader $sessionReader,
        private readonly CommandPoolInterface $commandPool,
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws CommandException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $response = $this->sessionReader->getResponse($response);
        $payment = SubjectReader::readPayment($handlingSubject)->getPayment();
        ContextHelper::assertOrderPayment($payment);
        if (isset($response[AbstractCommon::STATUS])) {
            if ($response[AbstractCommon::STATUS] === SessionStatus::STATUS_EXPIRED) {
                // Session Expired - cancel order.
                $command = $this->commandPool->get('order_cancel');
                $command->execute($handlingSubject);

                return;
            }
            if ($response[AbstractCommon::STATUS] === SessionStatus::STATUS_PAID) {
                $command = $this->commandPool->get('order_update');
                $command->execute($handlingSubject);
            }
        }
    }
}
