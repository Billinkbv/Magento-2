<?php

namespace Billink\Billink\Observer;

use Billink\Billink\Model\Ui\ConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use function in_array;

class SalesModelServiceQuoteSubmitObserver implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        if (in_array($order->getPayment()?->getMethod(), [ConfigProvider::CODE, ConfigProvider::CODE_MIDPAGE])) {
            $order->setBaseBillinkFeeAmount($quote->getBaseBillinkFeeAmount());
            $order->setBillinkFeeAmount($quote->getBillinkFeeAmount());
            $order->setBaseBillinkFeeAmountTax($quote->getBaseBillinkFeeAmountTax());
            $order->setBillinkFeeAmountTax($quote->getBillinkFeeAmountTax());
        }
    }
}
