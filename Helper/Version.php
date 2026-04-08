<?php

namespace Billink\Billink\Helper;

use Magento\Framework\Module\ModuleListInterface;

class Version
{
    public const MODULE_NAME = 'Billink_Billink';

    public function __construct(
        private readonly ModuleListInterface $moduleList
    ) {
    }

    public function getCurrentVersion(): string
    {
        return $this->moduleList
            ->getOne(self::MODULE_NAME)['setup_version'];
    }

    public function isSameAsCurrent(string $subject): bool
    {
        return $subject === $this->getCurrentVersion();
    }
}
