<?php

namespace Billink\Billink\Gateway\Response\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Model\Order\Payment\Transaction;

class SessionCreateHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    public function __construct(
        protected readonly SessionReader $sessionReader
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $response = $this->sessionReader->getResponse($response);
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $payment->setAdditionalInformation(
            SessionReader::REDIRECT_URL,
            $response[SessionReader::REDIRECT_URL]
        );
        $payment->addTransaction(Transaction::TYPE_ORDER);
    }
}
