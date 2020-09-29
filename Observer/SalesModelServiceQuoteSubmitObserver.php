<?php

namespace Billink\Billink\Observer;

use Billink\Billink\Model\Debug;
use Billink\Billink\Model\Ui\ConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class SalesModelServiceQuoteSubmitObserver
 * @package Billink\Billink\Observer
 */
class SalesModelServiceQuoteSubmitObserver implements ObserverInterface
{
    public function __construct(
        Debug $debug
    ) {
        $this->debug = $debug;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $this->debug->trace($order);

        if (ConfigProvider::CODE == $order->getPayment()->getMethod()) {
            $order->setBaseBillinkFeeAmount($quote->getBaseBillinkFeeAmount());
            $order->setBillinkFeeAmount($quote->getBillinkFeeAmount());
            $order->setBaseBillinkFeeAmountTax($quote->getBaseBillinkFeeAmountTax());
            $order->setBillinkFeeAmountTax($quote->getBillinkFeeAmountTax());
        }
    }
}