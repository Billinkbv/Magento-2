<?php
namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;

class Options implements BuilderInterface
{
    private MidpageConfig $midpageConfig;
    private StoreManagerInterface $storeManager;

    public function __construct(
        MidpageConfig $midpageConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->midpageConfig = $midpageConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $data = ['options' => [
            'logoURL' => $this->midpageConfig->getLogo($this->storeManager->getStore())
        ]];

        return ['client' => $data];
    }
}
