<?php

namespace Billink\Billink\Model;

use Magento\Sales\Api\Data\OrderInterface;

class LocalStorage
{
    private ?OrderInterface $order = null;

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?OrderInterface $order): void
    {
        $this->order = $order;
    }
}
