<?php

namespace Billink\Billink\Model\Total\Quote;

use Billink\Billink\Gateway\Config\Config;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class BillinkFee extends AbstractBillinkFee
{
    public function __construct(
        PriceCurrencyInterface $priceCurrencyInterface,
        \Billink\Billink\Model\Fee\BillinkFee $fee,
        Config $config
    ) {
        parent::__construct($priceCurrencyInterface, $fee, $config);
    }
}
