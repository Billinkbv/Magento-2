<?php

namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Billink\Billink\Gateway\Helper\TransactionManager;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class ReturnUrls implements BuilderInterface
{
    public function __construct(
        private readonly TransactionManager $transactionManager,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    public function build(array $buildSubject): array
    {
        $paymentOrder = SubjectReader::readPayment($buildSubject)->getOrder();

        $transactionId = $this->transactionManager->createTransactionId($paymentOrder->getOrderIncrementId());
        $params = ['txn' => $transactionId];
        $cancelUrl = $this->urlBuilder->getUrl('billink/midpage/cancel', $params);
        $data = [
            'successURL' => $this->urlBuilder->getUrl('billink/midpage/place', $params),
            'failURL' => $cancelUrl,
            'backURL' => $cancelUrl,
            'cancelURL' => $cancelUrl
        ];

        return ['client' => ['returnURL' => $data]];
    }
}
