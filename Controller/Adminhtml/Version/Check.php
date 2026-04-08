<?php

namespace Billink\Billink\Controller\Adminhtml\Version;

use Billink\Billink\Helper\Version as VersionHelper;
use Billink\Billink\Model\VersionCheckerInterface;
use Billink\Billink\Model\VersionCheckerInterfaceFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class Check extends Action
{
    public function __construct(
        Action\Context $context,
        private readonly VersionCheckerInterfaceFactory $versionCheckerFactory,
        private readonly VersionHelper $versionHelper,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        $versionChecker = $this->versionCheckerFactory->create();
        $versionInfo = [];

        try {
            $versionInfo = $this->prepareVersionInfo($versionChecker);
        } catch (Exception $e) {
            $versionInfo['error'] = 1;
            $this->logger->error('Version check error: ' . $e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($versionInfo);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Billink_Billink::resource');
    }

    private function prepareVersionInfo(VersionCheckerInterface $versionChecker): array
    {
        $remoteVersion = $versionChecker->getRemoteVersion();

        return [
            'error' => 0,
            'version' => $remoteVersion,
            'isUpToDate' => $this->versionHelper->isSameAsCurrent($remoteVersion)
        ];
    }
}
