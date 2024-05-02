<?php

namespace Billink\Billink\Observer;

use Billink\Billink\Model\Ui\ConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ShipmentObserver
 * @package Billink\Billink\Observer
 */
class ShipmentObserver implements ObserverInterface
{
    public const METHOD_CODE = ConfigProvider::CODE;

    public function __construct(
        private readonly GatewayCommand $startWorkflowCommand,
        private readonly LoggerInterface $logger,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        $payment = $this->getPayment($observer->getData('shipment'));

        $method = $payment->getMethodInstance();

        if ($method->getCode() !== static::METHOD_CODE) {
            return;
        }

        try {
            $originalStore = $this->storeManager->getStore()->getId();
            $this->storeManager->setCurrentStore($observer->getData('shipment')->getStoreId());

            $this->startWorkflowCommand->execute(['payment' => $payment]);

            // restore current store
            $this->storeManager->setCurrentStore($originalStore);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param \Magento\Shipping\Model\Shipment $shipment
     * @return mixed
     */
    private function getPayment($shipment)
    {
        return $shipment->getOrder()->getPayment();
    }
}
