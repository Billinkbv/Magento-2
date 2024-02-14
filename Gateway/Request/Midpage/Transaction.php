<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class Transaction implements BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        $orderId = $additionalInformation['id'] ?? '';
        $data = [
            'order_id' => $orderId
        ];
        return $data;
    }
}
