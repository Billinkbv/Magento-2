<?php
namespace Billink\Billink\Model\Total\Quote;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class BillinkMidpageFee
 * @package Billink\Billink\Model\Total\Quote
 */
class BillinkMidpageFee extends AbstractBillinkFee
{
    public function __construct(
        PriceCurrencyInterface $priceCurrencyInterface,
        \Billink\Billink\Model\Fee\BillinkFee $fee,
        MidpageConfig $config
    ) {
        parent::__construct($priceCurrencyInterface, $fee, $config);
    }
}
