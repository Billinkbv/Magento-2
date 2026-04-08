<?php

namespace Billink\Billink\Gateway\Converter\Order;

use Magento\Sales\Model\Order;

interface ConverterInterface
{
    public function convert(?Order $order = null): array;
}
