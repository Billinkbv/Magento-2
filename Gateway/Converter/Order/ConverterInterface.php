<?php

namespace Billink\Billink\Gateway\Converter\Order;

use Magento\Sales\Model\Order;
use Magento\Quote\Model\Quote;

interface ConverterInterface
{
    public function convert(Quote|Order|null $order = null): array;
}
