<?php

namespace Billink\Billink\Model\Total\Invoice;

use Billink\Billink\Gateway\Config\Config;

class BillinkFee extends AbstractBillinkFee
{
    public function __construct(
        Config $config
    ) {
        parent::__construct($config);
    }
}
