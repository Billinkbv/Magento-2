<?php

namespace Billink\Billink\Model;

use Billink\Billink\Logger\Handler\BillinkTraces;
use Magento\Sales\Api\Data\OrderInterface;

class Debug
{
    /** @var BillinkTraces */
    private $logger;

    public function __construct(
        BillinkTraces $logger
    ) {
        $this->logger = $logger;
    }

    public function trace(OrderInterface $order)
    {
        if (!$this->debugEnabled()) {
            return;
        }

        $this->logger->write(["AAAA"]);
        die("HOI");
    }
}