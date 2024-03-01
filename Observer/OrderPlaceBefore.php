<?php

namespace Billink\Billink\Observer;

use Billink\Billink\Model\LocalStorage;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderPlaceBefore implements ObserverInterface
{
    public function __construct(
        protected readonly LocalStorage $localStorage
    ) {
    }

    public function execute(Observer $observer): void
    {
        $this->localStorage->setOrder($observer->getData('order'));
    }

}
