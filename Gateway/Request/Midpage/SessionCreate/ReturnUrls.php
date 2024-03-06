<?php
namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Billink\Billink\Gateway\Helper\TransactionManager;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\UrlInterface;

class ReturnUrls implements BuilderInterface
{
    public function __construct(
        protected readonly TransactionManager $transactionManager,
        protected readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $paymentOrder = $paymentDO->getOrder();

        $transactionId = $this->transactionManager->createTransactionId($paymentOrder->getOrderIncrementId());
        $params = ['txn' => $transactionId];
        $cancelUrl = $this->urlBuilder->getUrl('billink/midpage/cancel', $params);
        $data = [
            'successURL' => $this->urlBuilder->getUrl('billink/midpage/place', $params),
            'failURL' => $cancelUrl,
            'backURL' => $cancelUrl,
            'cancelURL' => $cancelUrl
        ];

        return ['client' => ['returnURL' => $data]];
    }
}
