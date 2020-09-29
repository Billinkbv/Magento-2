<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class InvoiceDataBuilder implements BuilderInterface
{
    const INVOICES = 'INVOICES';
    const ITEM = 'ITEM';
    const INVOICE_NUMBER = 'INVOICENUMBER';
    const WORKFLOW_NUMBER = 'WORKFLOWNUMBER';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Workflow
     */
    private $workflowHelper;

    /**
     * InvoiceDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param WorkflowHelper $workflowHelper
     */
    public function __construct(
        SubjectReader $subjectReader,
        WorkflowHelper $workflowHelper
    ) {
        $this->subjectReader = $subjectReader;
        $this->workflowHelper = $workflowHelper;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        $workflowType = $this->subjectReader->readPaymentWorkflowType($buildSubject);

        $result = [
            self::INVOICES => [
                self::ITEM => [
                    self::INVOICE_NUMBER => $order->getIncrementId(),
                    self::WORKFLOW_NUMBER => $this->workflowHelper->getNumber($workflowType)
                ]
            ]
        ];

        return $result;
    }
}