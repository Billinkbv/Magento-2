<?php
namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;

class Options implements BuilderInterface
{
    public function __construct(
        protected readonly MidpageConfig $midpageConfig,
        protected readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $data = ['options' => [
            'logoURL' => $this->midpageConfig->getLogo($this->storeManager->getStore())
        ]];

        return ['client' => $data];
    }
}
