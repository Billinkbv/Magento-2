<?php

namespace Billink\Billink\Model;

use Magento\Framework\Module\ModuleListInterface;

class VersionChecker implements VersionCheckerInterface
{
    public const MODULE_NAME = 'Billink_Billink';

    public function __construct(
        private readonly ModuleListInterface $moduleList
    ) {
    }

    public function getRemoteVersion(): string
    {
        return $this->moduleList->getOne(static::MODULE_NAME)['setup_version'];
    }
}
