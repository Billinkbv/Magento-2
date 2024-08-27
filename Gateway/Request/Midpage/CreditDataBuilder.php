<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Billink\Billink\Gateway\Helper\SubjectReader;

class CreditDataBuilder extends Authorize
{
    public const INVOICES = 'invoices';

    public const INCREMENT_ID = 'number';
    public const AMOUNT = 'creditAmount';
    public const DESCRIPTION = 'description';

    public function __construct(
        MidpageConfig $midpageConfig,
        private readonly SubjectReader $subjectReader
    ) {
        parent::__construct($midpageConfig);
    }

    /**
     * Builds ENV request
     */
    public function build(array $buildSubject): array
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        $amount = $this->subjectReader->readRefundAmount($buildSubject);
        $payment = $this->subjectReader->readPayment($buildSubject);

        $creditmemo = $payment->getCreditmemo();
        $billinkFee = $creditmemo?->getData('billink_fee_amount');

        $return = [
            self::USER_NAME => $this->midpageConfig->getAccountName(),
            self::USER_ID => $this->midpageConfig->getAccountId(),
            self::INVOICES => [
                [
                    self::INCREMENT_ID => $order->getIncrementId(),
                    self::AMOUNT => $amount,
                    self::DESCRIPTION => 'Order refund via...'
                ]
            ]
        ];
        if ($billinkFee > 0) {
            $return['returnCosts'] = 1;
            $return['returnCostsAmount'] = $billinkFee;
        }
        return $return;
    }
}
