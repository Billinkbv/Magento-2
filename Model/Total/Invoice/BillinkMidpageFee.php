<?php

namespace Billink\Billink\Model\Total\Invoice;

use Billink\Billink\Gateway\Config\MidpageConfig;

/**
 * Class BillinkMidpageFee
 * @package Billink\Billink\Model\Total\Invoice
 */
class BillinkMidpageFee extends AbstractBillinkFee
{
    public function __construct(
        MidpageConfig $config
    ) {
        parent::__construct($config);
    }
}
