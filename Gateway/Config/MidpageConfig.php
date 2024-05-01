<?php

namespace Billink\Billink\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

/**
 * Class Config
 * @package Billink\Billink\Gateway\Config
 */
class MidpageConfig extends BasePaymentConfig
{
    public const MEDIA_FOLDER = 'billink';

    public const FIELD_LOGO = 'logo';

    public const TEST_MODE = 'test_mode';
    public const FIELD_API_VERSION = 'api_version';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        private readonly Repository $assetRepository,
        $methodCode = null,
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        \Magento\Payment\Gateway\Config\Config::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    public function getApiVersion(): string
    {
        return (string)$this->getValue(self::FIELD_API_VERSION);
    }

    /**
     * @param StoreInterface|null $store
     * @return string
     */
    public function getLogo(StoreInterface $store = null): string
    {
        $value = $this->getValue(self::FIELD_LOGO);

        if (!$value) {
            return $this->assetRepository->getUrl('Billink_Billink::images/billink-logo-default.svg');
        }

        if ($store instanceof Store) {
            $mediaPath = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            return $mediaPath . self::MEDIA_FOLDER . '/' . $value;
        }

        return $value;
    }

    public function isTestMode(): bool
    {
        return (bool)$this->getValue(static::TEST_MODE);
    }
}
