<?php
namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Billink\Billink\Gateway\Helper\TransactionManager;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\UrlInterface;

class ReturnUrls implements BuilderInterface
{
    private TransactionManager $transactionManager;
    private UrlInterface $urlBuilder;

    public function __construct(
        TransactionManager $transactionManager,
        UrlInterface $urlBuilder
    ) {
        $this->transactionManager = $transactionManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
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
        file_put_contents('/var/www/m2.test/m2/logs/keys.txt', print_r($data, 1). "\n\n", FILE_APPEND);

        return ['client' => ['returnURL' => $data]];
    }
}
