<?php

namespace Billink\Billink\Gateway\Response\Order;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Validator\OrderDataValidator;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Framework\DB\TransactionFactory;

/**
 * Class Handler
 * @package Billink\Billink\Gateway\Response\Order
 */
class Handler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * Handler constructor.
     * @param SubjectReader $subjectReader
     * @param Config $config
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config,
        TransactionFactory $transactionFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (isset($handlingSubject[OrderDataValidator::INDEX_FLAG_VALIDATION])) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->subjectReader->readOrder($handlingSubject);
        $order->addStatusHistoryComment('Order was created in Billink system.');

        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice()
                ->register()
                ->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
            $this->transactionFactory->create()
                ->addObject($order)
                ->addObject($invoice)
                ->save();
            $order->addStatusHistoryComment('Invoice was automatically registered and set to paid');
        }
    }
}