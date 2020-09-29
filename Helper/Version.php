<?php

namespace Billink\Billink\Helper;

use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Version
 * @package Billink\Billink\Helper
 */
class Version
{
    const MODULE_NAME = 'Billink_Billink';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * Version constructor.
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * @return mixed
     */
    public function getCurrentVersion()
    {
        return $this->moduleList
            ->getOne(self::MODULE_NAME)['setup_version'];
    }

    /**
     * @param string $subject
     * @return bool
     */
    public function isSameAsCurrent($subject)
    {
        return $subject === $this->getCurrentVersion();
    }
}