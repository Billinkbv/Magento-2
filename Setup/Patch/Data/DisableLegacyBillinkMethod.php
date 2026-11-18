<?php

namespace Billink\Billink\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * The legacy "billink" payment method was retired in favor of "billink_midpage" (new orders can no longer
 * select it), but merchants who had it enabled still have payment/billink/active=1 stored from before.
 * Since Magento's active-method list is built from the whole stored payment/* config tree (not from
 * whether a system.xml group exists), that stale value would make it selectable again. Force it off here
 * so retiring the method doesn't depend on someone remembering a manual config change per environment.
 */
class DisableLegacyBillinkMethod implements DataPatchInterface
{
    private const PATH_ACTIVE = 'payment/billink/active';
    private const PATH_CAN_USE_CHECKOUT = 'payment/billink/can_use_checkout';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['path IN (?)' => [self::PATH_ACTIVE, self::PATH_CAN_USE_CHECKOUT]]
        );

        $this->configWriter->save(self::PATH_ACTIVE, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->configWriter->save(self::PATH_CAN_USE_CHECKOUT, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
