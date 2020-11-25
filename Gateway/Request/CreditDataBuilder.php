<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class CreditDataBuilder implements BuilderInterface
{
    const INVOICES = 'INVOICES';
    const ITEM = 'ITEM';
    const INVOICE_NUMBER = 'INVOICENUMBER';
    const CREDITAMOUNT = 'CREDITAMOUNT';
    const DESCRIPTION = 'DESCRIPTION';
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * InvoiceDataBuilder constructor.
     *
     * @param SubjectReader  $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        $amount = $this->subjectReader->readRefundAmount($buildSubject);

        $result = [
            self::INVOICES => [
                self::ITEM => [
                    self::INVOICE_NUMBER  => $order->getIncrementId(),
                    self::CREDITAMOUNT => $amount,
                ]
            ]
        ];

        return $result;
    }
}
