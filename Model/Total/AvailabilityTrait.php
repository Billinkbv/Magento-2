<?php

namespace Billink\Billink\Model\Total;

use Billink\Billink\Model\Ui\ConfigProvider;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

trait AvailabilityTrait
{
    protected function isApplicable(Quote|Order $subject): bool
    {
        if (!$this->config->getIsFeeActive()) {
            return false;
        }
        $code = $this->getCode();

        return
            ($code === 'billink_fee' && $subject->getPayment()->getMethod() === ConfigProvider::CODE)
            || ($code === 'billink_midpage_fee'
                && $subject->getPayment()->getMethod() === ConfigProvider::CODE_MIDPAGE);
    }
}
