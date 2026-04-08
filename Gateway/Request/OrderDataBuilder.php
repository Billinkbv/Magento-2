<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Gateway\Request\BuilderInterface;

use function round;

class OrderDataBuilder implements BuilderInterface
{
    public const ORDERAMOUNT = 'ORDERAMOUNT';
    public const ORDERNUMBER = 'ORDERNUMBER';
    public const DATE = 'DATE';

    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly DateTime $datetime
    ) {
    }

    public function build(array $buildSubject): array
    {
        $payment = $this->subjectReader->readPayment($buildSubject);
        $validationFlag = $this->subjectReader->readValidationFlag($buildSubject);

        $orderData = $payment->getQuote() ?: $payment->getOrder();

        $result = [
            self::DATE => $this->datetime->date('d-m-Y')
        ];

        if ($orderData->getIncrementId()) {
            $result[self::ORDERNUMBER] = $orderData->getIncrementId();
        } else {
            $result[self::ORDERAMOUNT] = round($orderData->getGrandTotal(), 2);
        }

        if ($validationFlag) {
            $result[self::ORDERNUMBER] = 'validation';
        }

        return $result;
    }
}
