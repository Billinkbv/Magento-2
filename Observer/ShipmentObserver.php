<?php

namespace Billink\Billink\Observer;

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
    /**
     * @var GatewayCommand
     */
    private $startWorkflowCommand;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ShipmentObserver constructor.
     * @param GatewayCommand $startWorkflowCommand
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GatewayCommand $startWorkflowCommand,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->startWorkflowCommand = $startWorkflowCommand;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $payment = $this->getPayment($observer->getData('shipment'));

        $method = $payment->getMethodInstance();

        if ($method->getCode() != "billink") {
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