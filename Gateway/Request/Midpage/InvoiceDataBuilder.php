<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Gateway\Request\Workflow;
use Billink\Billink\Model\Ui\ConfigProvider;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class InvoiceDataBuilder implements BuilderInterface
{
    public const INVOICES = 'INVOICES';
    public const ITEM = 'ITEM';
    public const INVOICE_NUMBER = 'INVOICENUMBER';

    public function __construct(
        private readonly SubjectReader $subjectReader
    ) {
    }

    /**
     * Builds ENV request
     */
    public function build(array $buildSubject): array
    {
        $order = $this->subjectReader->readOrder($buildSubject);

        return [
            self::INVOICES => [
                self::ITEM => [
                    self::INVOICE_NUMBER => $order->getIncrementId(),
                ]
            ]
        ];
    }
}
