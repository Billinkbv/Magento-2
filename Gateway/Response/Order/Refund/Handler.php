<?php

namespace Billink\Billink\Gateway\Response\Order\Refund;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Validator\OrderDataValidator;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Framework\DB\TransactionFactory;

/**
 * Class Handler
 *
 * @package Billink\Billink\Gateway\Response\Order\Refund
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
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (isset($handlingSubject[OrderDataValidator::INDEX_FLAG_VALIDATION])) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->subjectReader->readOrder($handlingSubject);
        $order->addStatusHistoryComment('Refund was created in Billink system.');

        $payment = $order->getPayment();
        // +1 because the creditmemo is not created yet
        $idx = $order->getCreditmemosCollection()->getSize() + 1;
        $payment->setLastTransId('billink-refund-' . $order->getIncrementId() . '-' . $idx);
    }
}
