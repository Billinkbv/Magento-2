<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class OrderDataBuilder implements BuilderInterface
{
    const ORDERAMOUNT = 'ORDERAMOUNT';
    const ORDERNUMBER = 'ORDERNUMBER';
    const DATE = 'DATE';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var DateTime
     */
    private $datetime;

    /**
     * OrderDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param DateTime $datetime
     */
    public function __construct(
        SubjectReader $subjectReader,
        DateTime $datetime
    ) {

        $this->subjectReader = $subjectReader;
        $this->datetime = $datetime;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->subjectReader->readPayment($buildSubject);
        $validationFlag = $this->subjectReader->readValidationFlag($buildSubject);

        $orderData = $payment->getQuote() ?: $payment->getOrder();

        $result = [
            self::DATE => $this->datetime->date('d-m-Y')
        ];

        if ($orderData->getIncrementId()) {
            $result = array_merge($result, [
                self::ORDERNUMBER => $orderData->getIncrementId()
            ]);
        } else {
            $result = array_merge($result, [
                self::ORDERAMOUNT => round($orderData->getGrandTotal(), 2)
            ]);
        }

        if ($validationFlag) {
            $result[self::ORDERNUMBER] = 'validation';
        }

        return $result;
    }
}