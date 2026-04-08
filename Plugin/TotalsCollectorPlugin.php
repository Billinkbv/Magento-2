<?php

namespace Billink\Billink\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;

class TotalsCollectorPlugin
{
    public function beforeCollect(
        TotalsCollector $subject,
        Quote $quote
    ): void {
        $quote->setBillinkFeeAmount(0);
        $quote->setBaseBillinkFeeAmount(0);

        $quote->setBillinkFeeAmountTax(0);
        $quote->setBaseBillinkFeeAmountTax(0);
    }
}
