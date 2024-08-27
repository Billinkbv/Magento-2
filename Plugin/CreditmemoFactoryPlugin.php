<?php

namespace Billink\Billink\Plugin;

use Magento\Framework\Locale\FormatInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory as SubjectClass;

class CreditmemoFactoryPlugin
{
    public function __construct(
        private readonly FormatInterface $localeFormat
    ) {
    }


    public function afterCreateByOrder(
        SubjectClass $subject,
        Creditmemo $result,
        \Magento\Sales\Model\Order $order,
        array $data = []
    ): Creditmemo {
        $this->attachBillinkFees($result, $data);
        return $result;
    }

    public function afterCreateByInvoice(
        SubjectClass $subject,
        Creditmemo $result,
        \Magento\Sales\Model\Order\Invoice $invoice,
        array $data = []
    ): Creditmemo {
        $this->attachBillinkFees($result, $data);
        return $result;
    }

    private function attachBillinkFees(Creditmemo $creditmemo, array $data): void
    {
        if (isset($data['billink_fee_amount'])) {
            $adjustmentNegativeAmount = $this->parseNumber($data['billink_fee_amount']);
            $creditmemo->setData('billink_fee_amount', $adjustmentNegativeAmount);
        }
    }

    private function parseNumber(?string $value): ?float
    {
        return $this->localeFormat->getNumber($value);
    }
}
