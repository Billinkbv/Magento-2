<?php

namespace Billink\Billink\Model\Total;

/**
 * Trait AvailabilityTrait
 * @package Billink\Billink\Model\Total
 */
trait AvailabilityTrait
{
    /**
     * @param \Magento\Sales\Model\Order|\Magento\Quote\Model\Quote $subject
     * @return bool
     */
    protected function isApplicable($subject)
    {
        if (!$this->config->getIsFeeActive()) {
            return false;
        }

        return ($subject->getPayment()->getMethod() == \Billink\Billink\Model\Ui\ConfigProvider::CODE);
    }
}