<?php

namespace Billink\Billink\Gateway\Response\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Billink\Billink\Gateway\Validator\Midpage\SessionCreate;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class SessionCreateHandler implements HandlerInterface
{
    public function __construct(
        private readonly SessionReader $sessionReader
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $response = $this->sessionReader->getResponse($response);
        $payment = SubjectReader::readPayment($handlingSubject)->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $payment->setAdditionalInformation(
            SessionReader::REDIRECT_URL,
            $response[SessionReader::REDIRECT_URL]
        );
        $payment->setAdditionalInformation(
            SessionCreate::SESSION_ID,
            $response[SessionCreate::SESSION_ID]
        );
        $payment->setLastTransId(
            $response[SessionCreate::INVOICE]
        );
        $payment->addTransaction(TransactionInterface::TYPE_ORDER);
    }
}
