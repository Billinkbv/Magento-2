<?php

namespace Billink\Billink\Gateway\Config;

use Billink\Billink\Model\Config\Source\UsedWorkflow;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\Store;
use Magento\Store\Model\Store\Interceptor;

/**
 * Class Config
 * @package Billink\Billink\Gateway\Config
 */
class Config extends BasePaymentConfig
{
    const MEDIA_FOLDER = 'billink';

    const FIELD_API_VERSION = 'api_version';
    const FIELD_LOGO = 'logo';
    const FIELD_BACKDOOR = 'debug_backdoor';
    const FIELD_WORKFLOW = 'workflow';
    const FIELD_ORDER_STATUS = 'order_status';
    const FIELD_IS_ALTERNATE_DELIVERY_ADDRESS_ALLOWED = 'is_alternate_delivery_address_allowed';
    const FIELD_ALLOW_SPECIFIC = 'allowspecific';
    const FIELD_SPECIFIC_COUNTRY = 'specificcountry';
    const FIELD_IS_TOTALCHECK_ACTIVE = 'is_totalcheck_active';
    const FIELD_IS_INVOICE_EMAIL_ENABLED = 'is_invoice_email_enabled';
    const FIELD_USED_WORKFLOW = 'use_workflow';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        private readonly Repository $assetRepository,
        $methodCode = null,
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        \Magento\Payment\Gateway\Config\Config::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->getValue(self::FIELD_API_VERSION);
    }

    /**
     * @param Interceptor|null $store
     * @return string
     */
    public function getLogo(Interceptor $store = null)
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
    public function getWorkflow($storeId = null)
    {
        $workflowSettings = json_decode($this->getValue(self::FIELD_WORKFLOW), true);

        if (!is_array($workflowSettings) || empty($workflowSettings)) {
            return [];
        }

        $availableWorkflows = $this->getUsedWorkflow($storeId);

        switch ($availableWorkflows) {
            case UsedWorkflow::CONFIG_WORKFLOW_PRIVATE:
                if(isset($workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_PRIVATE])) {
                    $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_PRIVATE]['type'] = __($workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_PRIVATE]['type']);

                    return [UsedWorkflow::CONFIG_WORKFLOW_PRIVATE => $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_PRIVATE]];
                }
                return [];
            case UsedWorkflow::CONFIG_WORKFLOW_BUSINESS:
                if(isset($workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_BUSINESS])) {
                    $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_BUSINESS]['type'] = __($workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_BUSINESS]['type']);

                    return [UsedWorkflow::CONFIG_WORKFLOW_BUSINESS => $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_BUSINESS]];
                }
                return [];
            default:
                foreach ($workflowSettings as $key => $workflow) {
                    $workflowSettings[$key]['type'] = __($workflow['type']);
                }
        }

        return $workflowSettings;
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
     * @param int $storeId
     * @return bool
     */
    public function getIsInvoiceEmailEnabled($storeId = null)
    {
        return !!$this->getValue(self::FIELD_IS_INVOICE_EMAIL_ENABLED, $storeId);
    }

    /**
     * @param int $storeId
     * @return ?string
     */
    public function getUsedWorkflow($storeId = null)
    {
        return $this->getValue(self::FIELD_USED_WORKFLOW, $storeId);
    }
}
