<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Magento\Payment\Gateway\Helper\SubjectReader;

class Status extends Authorize
{
    public const INVOICE_FIELD = 'invoiceNumber';
    public const BILLINK_INVOICE_FIELD = 'billinkInvoiceNumber';
    public const SESSION_FIELD = 'sessionID';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $data = [];
        $data[self::USER_NAME] = $this->midpageConfig->getAccountName();
        $data[self::USER_ID] = $this->midpageConfig->getAccountId();
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data[self::BILLINK_INVOICE_FIELD] = $payment->getLastTransId();
        return $data;
    }
}
