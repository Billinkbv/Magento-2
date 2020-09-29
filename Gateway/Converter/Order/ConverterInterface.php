<?php

namespace Billink\Billink\Gateway\Converter\Order;

interface ConverterInterface
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    public function convert($order);
}