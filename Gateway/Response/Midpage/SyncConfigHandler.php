<?php

namespace Billink\Billink\Gateway\Response\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Billink\Billink\Gateway\Validator\Midpage\SyncConfig;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Zend_Db_Expr;

class SyncConfigHandler implements HandlerInterface
{
    public function __construct(
        private readonly SessionReader $reader,
        private readonly CollectionFactory $orderCollectionFactory,
        private readonly WriterInterface $configWriter,
        private readonly TypeListInterface $typeList
    ) {
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $response = $this->reader->getResponse($response);
        if ($response[SyncConfig::WHITELIST]) {
            // Time to validate!
            $collection = $this->orderCollectionFactory->create();
            $collection
                ->addFieldToSelect(['entity_id', 'total_invoiced'])
                // Paid orders
                ->addFieldToFilter('total_invoiced', ['gt' => 0])
                // Last X orders
                ->setOrder('created_at')
                ->setPageSize($response[SyncConfig::MIN_PAID_ORDERS]);
            if ($response[SyncConfig::PERIOD] > 0) {
                // Filter period
                $collection->getSelect()
                    ->where(new Zend_Db_Expr('DATEDIFF(NOW(), created_at) < ' . $response[SyncConfig::PERIOD]));
            }
            $totalPaid = 0;
            foreach ($collection as $orderData) {
                $totalPaid += $orderData->getData('total_invoiced');
            }

            $score = $totalPaid > $response[SyncConfig::TOTAL_AMOUNT] ? 10 : 0;
            $this->configWriter->save('payment/billink_midpage/trust_score', $score);
            $this->typeList->invalidate(Config::TYPE_IDENTIFIER);
        }
    }
}
