<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Validator\OrderDataValidator;
use Billink\Billink\Observer\DataAssignObserver;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;

class SubjectReader
{
    public const INDEX_PAYMENT = 'payment';
    public const INDEX_ADDITIONAL_INFO = 'additional_information';
    public const INDEX_REFUND_AMOUNT = 'amount';

    public const INDEX_INVOICE_ID = 'invoice_id';

    public function readPayment(array $subject): mixed
    {
        if (!isset($subject[self::INDEX_PAYMENT])) {
            throw new InvalidArgumentException('Payment object does not exists');
        }

        return $subject[self::INDEX_PAYMENT]->getPayment() ?: $subject[self::INDEX_PAYMENT];
    }

    public function readPaymentAdditionalInformation(array $subject): array
    {
        $payment = $this->readPayment($subject);

        return $payment[self::INDEX_ADDITIONAL_INFO] ?? [];
    }

    public function readPaymentAIField(string $index, array $subject): mixed
    {
        $paymentAI = $this->readPaymentAdditionalInformation($subject);

        return $paymentAI[$index] ?? false;
    }

    public function readPaymentWorkflowType(array $subject): mixed
    {
        $paymentAI = $this->readPaymentAdditionalInformation($subject);

        return $paymentAI[DataAssignObserver::CUSTOMER_TYPE] ?? false;
    }

    /**
     * @throws LocalizedException
     */
    public function readPaymentCheckUUID(array $subject): mixed
    {
        $paymentAI = $this->readPaymentAdditionalInformation($subject);

        if (!isset($paymentAI[Gateway::CHECKUUID])) {
            throw new LocalizedException(__('Missing UUID'));
        }

        return $paymentAI[Gateway::CHECKUUID];
    }

    public function readOrder(array $subject): mixed
    {
        return $this->readPayment($subject)->getOrder();
    }

    public function readRefundAmount(array $subject): float
    {
        return (float) $subject[self::INDEX_REFUND_AMOUNT];
    }

    public function readValidationFlag(array $subject): mixed
    {
        return $subject[OrderDataValidator::INDEX_FLAG_VALIDATION] ?? false;
    }

    public function readResponse(array $subject): mixed
    {
        if (!isset($subject['response']) && !isset($subject['response']['result'])) {
            if (isset($subject['result'])) {
                return $subject['result'];
            }

            throw new InvalidArgumentException('Response data does not exists');
        }

        return $subject['response']['result'];
    }
}
