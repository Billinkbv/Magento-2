<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

class InvoiceDataBuilder implements BuilderInterface
{
    public const INVOICES = 'INVOICES';
    public const ITEM = 'ITEM';
    public const INVOICE_NUMBER = 'INVOICENUMBER';
    public const WORKFLOW_NUMBER = 'WORKFLOWNUMBER';

    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly WorkflowHelper $workflowHelper
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        $workflowType = $this->subjectReader->readPaymentWorkflowType($buildSubject);

        return [
            self::INVOICES => [
                self::ITEM => [
                    self::INVOICE_NUMBER => $order->getIncrementId(),
                    self::WORKFLOW_NUMBER => $this->workflowHelper->getNumber($workflowType)
                ]
            ]
        ];
    }
}
