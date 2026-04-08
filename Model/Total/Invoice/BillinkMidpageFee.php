<?php

namespace Billink\Billink\Model\Total\Invoice;

use Billink\Billink\Gateway\Config\MidpageConfig;

class BillinkMidpageFee extends AbstractBillinkFee
{
    public function __construct(
        MidpageConfig $config
    ) {
        parent::__construct($config);
    }
}
