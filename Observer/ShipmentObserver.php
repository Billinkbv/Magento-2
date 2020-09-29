<?php

namespace Billink\Billink\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Psr\Log\LoggerInterface;

/**
 * Class ShipmentObserver
 * @package Billink\Billink\Observer
 */
class ShipmentObserver implements \Magento\Framework\Event\ObserverInterface
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
     * ShipmentObserver constructor.
     * @param GatewayCommand $startWorkflowCommand
     * @param LoggerInterface $logger
     */
    public function __construct(
        GatewayCommand $startWorkflowCommand,
        LoggerInterface $logger
    ) {
        $this->startWorkflowCommand = $startWorkflowCommand;
        $this->logger = $logger;
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

        if($method->getCode() != "billink")
        {
            return true;
        }

        try {
            $this->startWorkflowCommand->execute(['payment' => $payment]);
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