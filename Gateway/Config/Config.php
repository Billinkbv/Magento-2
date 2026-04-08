<?php

namespace Billink\Billink\Gateway\Config;

use Billink\Billink\Model\Config\Source\UsedWorkflow;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Gateway\Config\Config as MagentoConfig;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

use function __;
use function is_array;
use function json_decode;

class Config extends BasePaymentConfig
{
    public const MEDIA_FOLDER = 'billink';

    public const FIELD_API_VERSION = 'api_version';
    public const FIELD_LOGO = 'logo';
    public const FIELD_BACKDOOR = 'debug_backdoor';
    public const FIELD_WORKFLOW = 'workflow';
    public const FIELD_ORDER_STATUS = 'order_status';
    public const FIELD_IS_ALTERNATE_DELIVERY_ADDRESS_ALLOWED = 'is_alternate_delivery_address_allowed';
    public const FIELD_ALLOW_SPECIFIC = 'allowspecific';
    public const FIELD_SPECIFIC_COUNTRY = 'specificcountry';
    public const FIELD_IS_TOTALCHECK_ACTIVE = 'is_totalcheck_active';
    public const FIELD_IS_INVOICE_EMAIL_ENABLED = 'is_invoice_email_enabled';
    public const FIELD_USED_WORKFLOW = 'use_workflow';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        private readonly Repository $assetRepository,
        $methodCode = null,
        $pathPattern = MagentoConfig::DEFAULT_PATH_PATTERN
    ) {
        MagentoConfig::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    public function getApiVersion(): string
    {
        return (string) $this->getValue(self::FIELD_API_VERSION);
    }

    public function getLogo(?StoreInterface $store = null): string
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

    public function getBackdoorOption(): mixed
    {
        return $this->getValue(self::FIELD_BACKDOOR);
    }

    public function getWorkflow(?int $storeId = null): array
    {
        $workflowSettings = json_decode($this->getValue(self::FIELD_WORKFLOW), true);

        if (!is_array($workflowSettings) || count($workflowSettings) === 0) {
            return [];
        }

        $availableWorkflows = $this->getUsedWorkflow($storeId);

        switch ($availableWorkflows) {
            case UsedWorkflow::CONFIG_WORKFLOW_PRIVATE:
                if (isset($workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_PRIVATE])) {
                    $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_PRIVATE]['type'] = __(
                        $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_PRIVATE]['type']
                    );

                    return [
                        UsedWorkflow::CONFIG_WORKFLOW_PRIVATE => $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_PRIVATE]
                    ];
                }

                return [];
            case UsedWorkflow::CONFIG_WORKFLOW_BUSINESS:
                if (isset($workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_BUSINESS])) {
                    $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_BUSINESS]['type'] = __(
                        $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_BUSINESS]['type']
                    );

                    return [
                        UsedWorkflow::CONFIG_WORKFLOW_BUSINESS => $workflowSettings[UsedWorkflow::CONFIG_WORKFLOW_BUSINESS]
                    ];
                }

                return [];
            default:
                foreach ($workflowSettings as $key => $workflow) {
                    $workflowSettings[$key]['type'] = __($workflow['type']);
                }
        }

        return $workflowSettings;
    }

    public function getOrderStatus(): string
    {
        return (string) $this->getValue(self::FIELD_ORDER_STATUS);
    }

    public function getIsAlternateDeliveryAddressAllowed(): bool
    {
        return (bool) $this->getValue(self::FIELD_IS_ALTERNATE_DELIVERY_ADDRESS_ALLOWED);
    }

    public function getAllowSpecific(): string
    {
        return (string) $this->getValue(self::FIELD_ALLOW_SPECIFIC);
    }

    public function getSpecificCountry(): string
    {
        return (string) $this->getValue(self::FIELD_SPECIFIC_COUNTRY);
    }

    public function getIsTotalcheckActive(): bool
    {
        return (bool) $this->getValue(self::FIELD_IS_TOTALCHECK_ACTIVE);
    }

    public function getIsInvoiceEmailEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::FIELD_IS_INVOICE_EMAIL_ENABLED, $storeId);
    }

    public function getUsedWorkflow(?int $storeId = null): ?string
    {
        return $this->getValue(self::FIELD_USED_WORKFLOW, $storeId);
    }
}
