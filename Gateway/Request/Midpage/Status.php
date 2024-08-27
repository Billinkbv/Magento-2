<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Validator\Midpage\SessionCreate;
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
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $sessionId = $payment->getAdditionalInformation(SessionCreate::SESSION_ID);
        if ($sessionId) {
            $data[self::SESSION_FIELD] = $sessionId;
        } else {
            // Fall back and compatibility with old orders
            $data[self::BILLINK_INVOICE_FIELD] = $payment->getLastTransId();
        }
        return $data;
    }
}
