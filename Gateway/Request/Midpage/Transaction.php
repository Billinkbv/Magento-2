<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class Transaction implements BuilderInterface
{
    public function build(array $buildSubject): array
    {
        $additionalInformation = SubjectReader::readPayment($buildSubject)->getPayment()->getAdditionalInformation();
        $orderId = $additionalInformation['id'] ?? '';

        return [
            'order_id' => $orderId
        ];
    }
}
