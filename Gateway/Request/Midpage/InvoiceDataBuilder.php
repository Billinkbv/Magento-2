<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class InvoiceDataBuilder implements BuilderInterface
{
    public const INVOICES = 'invoices';
    public const INVOICE_NUMBER = 'number';

    public function __construct(
        private readonly SubjectReader $subjectReader
    ) {
    }

    public function build(array $buildSubject): array
    {
        $order = $this->subjectReader->readOrder($buildSubject);

        return [
            self::INVOICES => [
                [
                    self::INVOICE_NUMBER => $order->getIncrementId(),
                ]
            ]
        ];
    }
}
