<?php


namespace Billink\Billink\Model;

use Magento\Framework\Module\ModuleListInterface;

class VersionChecker implements \Billink\Billink\Model\VersionCheckerInterface
{
    const MODULE_NAME = 'Billink_Billink';

    protected $moduleList;

    public function __construct(ModuleListInterface $moduleList)
    {
        $this->moduleList = $moduleList;

    }
    /**
     * @return mixed
     */
    public function getRemoteVersion()
    {
        return $this->moduleList->getOne(static::MODULE_NAME)['setup_version'];
    }
}