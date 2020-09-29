<?php

namespace Billink\Billink\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * Class TotalsCollectorPlugin
 * @package Billink\Billink\Plugin
 */
class TotalsCollectorPlugin
{
    /**
     * @param TotalsCollector $subject
     * @param Quote $quote
     * @return void
     */
    public function beforeCollect(
        TotalsCollector $subject,
        Quote $quote
    ) {
        $quote->setBillinkFeeAmount(0);
        $quote->setBaseBillinkFeeAmount(0);

        $quote->setBillinkFeeAmountTax(0);
        $quote->setBaseBillinkFeeAmountTax(0);
    }
}