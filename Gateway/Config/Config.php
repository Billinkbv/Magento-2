<?php

namespace Billink\Billink\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\Store;
use Magento\Store\Model\Store\Interceptor;

/**
 * Class Config
 * @package Billink\Billink\Gateway\Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const MEDIA_FOLDER = 'billink';

    const FIELD_API_VERSION = 'api_version';
    const FIELD_ACTIVE = 'is_active';
    const FIELD_LOGO = 'logo';
    const FIELD_ACCOUNT_NAME = 'account_name';
    const FIELD_ACCOUNT_ID = 'account_id';
    const FIELD_DEBUG = 'debug';
    const FIELD_BACKDOOR = 'debug_backdoor';
    const FIELD_WORKFLOW = 'workflow';
    const FIELD_ORDER_STATUS = 'order_status';
    const FIELD_IS_ALTERNATE_DELIVERY_ADDRESS_ALLOWED = 'is_alternate_delivery_address_allowed';
    const FIELD_ALLOW_SPECIFIC = 'allowspecific';
    const FIELD_SPECIFIC_COUNTRY = 'specificcountry';
    const FIELD_IS_TOTALCHECK_ACTIVE = 'is_totalcheck_active';
    const FIELD_IS_FEE_ACTIVE = 'is_fee_active';
    const FIELD_FEE_LABEL = 'fee_label';
    const FIELD_FEE_TYPE = 'fee_type';
    const FIELD_FEE_TAX_CLASS = 'fee_tax_class';
    const FIELD_FEE_RANGE = 'fee_range';
    const FIELD_IS_INVOICE_EMAIL_ENABLED = 'is_invoice_email_enabled';

    /**
     * @var Repository
     */
    private $assetRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepository,
        $methodCode = null,
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        \Magento\Payment\Gateway\Config\Config::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->assetRepository = $assetRepository;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->getValue(self::FIELD_API_VERSION);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getValue(self::FIELD_ACTIVE);
    }

    /**
     * @param Interceptor|null $store
     * @return string
     */
    public function getLogo(Interceptor $store = null)
    {
        $value = $this->getValue(self::FIELD_LOGO);

        if (!$value) {
            return $this->assetRepository->getUrl('Billink_Billink::images/logo.png');
        }

        if ($store instanceof Store) {
            $mediaPath = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            return $mediaPath . self::MEDIA_FOLDER . '/' . $value;
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getAccountName()
    {
        return $this->getValue(self::FIELD_ACCOUNT_NAME);
    }

    /**
     * @return string
     */
    public function getAccountId()
    {
        return $this->getValue(self::FIELD_ACCOUNT_ID);
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return (bool)$this->getValue(self::FIELD_DEBUG);
    }

    /**
     * @return string
     */
    public function getBackdoorOption()
    {
        return $this->getValue(self::FIELD_BACKDOOR);
    }

    /**
     * @return array
     */
    public function getWorkflow()
    {
        $result = json_decode($this->getValue(self::FIELD_WORKFLOW), true);

        if (!is_array($result) || empty($result)) {
            return [];
        }

        foreach ($result as $key => $workflow) {
            $result[$key]['type'] = __($workflow['type']);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->getValue(self::FIELD_ORDER_STATUS);
    }

    /**
     * @return bool
     */
    public function getIsAlternateDeliveryAddressAllowed()
    {
        return (bool)$this->getValue(self::FIELD_IS_ALTERNATE_DELIVERY_ADDRESS_ALLOWED);
    }

    /**
     * @return string
     */
    public function getAllowSpecific()
    {
        return $this->getValue(self::FIELD_ALLOW_SPECIFIC);
    }

    /**
     * @return string
     */
    public function getSpecificCountry()
    {
        return $this->getValue(self::FIELD_SPECIFIC_COUNTRY);
    }

    /**
     * @return bool
     */
    public function getIsTotalcheckActive()
    {
        return (bool)$this->getValue(self::FIELD_IS_TOTALCHECK_ACTIVE);
    }

    /**
     * @return string
     */
    public function getIsFeeActive()
    {
        return (bool)$this->getValue(self::FIELD_IS_FEE_ACTIVE);
    }

    /**
     * @return string|\Magento\Framework\Phrase
     */
    public function getFeeLabel()
    {
        return $this->getValue(self::FIELD_FEE_LABEL) ?: __('Billink Service Fee');
    }

    /**
     * @return string
     */
    public function getFeeType()
    {
        return $this->getValue(self::FIELD_FEE_TYPE);
    }

    /**
     * @return string
     */
    public function getFeeTaxClass()
    {
        return $this->getValue(self::FIELD_FEE_TAX_CLASS);
    }

    /**
     * @return array
     */
    public function getFeeRange()
    {
        return json_decode($this->getValue(self::FIELD_FEE_RANGE), true);
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function getIsInvoiceEmailEnabled($storeId = null)
    {
        return !!$this->getValue(self::FIELD_IS_INVOICE_EMAIL_ENABLED, $storeId);
    }
}