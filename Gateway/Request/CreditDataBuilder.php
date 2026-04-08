<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CreditDataBuilder implements BuilderInterface
{
    public const INVOICES = 'INVOICES';
    public const ITEM = 'ITEM';
    public const INVOICE_NUMBER = 'INVOICENUMBER';
    public const CREDITAMOUNT = 'CREDITAMOUNT';
    public const DESCRIPTION = 'DESCRIPTION';

    public function __construct(
        private readonly SubjectReader $subjectReader
    ) {
    }

    public function build(array $buildSubject): array
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        $amount = $this->subjectReader->readRefundAmount($buildSubject);

        return [
            self::INVOICES => [
                self::ITEM => [
                    self::INVOICE_NUMBER => $order->getIncrementId(),
                    self::CREDITAMOUNT => $amount,
                ]
            ]
        ];
    }
}
