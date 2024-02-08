<?php
namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\UrlInterface;

class ReturnUrls implements BuilderInterface
{
    private UrlInterface $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $data = [];
        $data['successURL'] = $this->urlBuilder->getUrl('billink/midpage/place');
        $data['failURL'] = $this->urlBuilder->getUrl('billink/midpage/cancel');
        $data['backURL'] = $this->urlBuilder->getUrl('billink/midpage/cancel');
        $data['cancelURL'] = $this->urlBuilder->getUrl('billink/midpage/cancel');

        return ['client' => ['returnURL' => $data]];
    }
}
