<?php

namespace Billink\Billink\Controller\Adminhtml\Version;

use Billink\Billink\Helper\Version as VersionHelper;
use Billink\Billink\Model\VersionCheckerInterface;
use Billink\Billink\Model\VersionCheckerInterfaceFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Check
 * @package Billink\Billink\Controller\Adminhtml\Version
 */
class Check extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var VersionCheckerInterface
     */
    private $versionCheckerFactory;

    /**
     * @var VersionHelper
     */
    private $versionHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Check constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param VersionCheckerInterfaceFactory $versionCheckerFactory
     * @param VersionHelper $versionHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        VersionCheckerInterfaceFactory $versionCheckerFactory,
        VersionHelper $versionHelper,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->versionCheckerFactory = $versionCheckerFactory;
        $this->versionHelper = $versionHelper;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $versionChecker = $this->versionCheckerFactory->create();
        $versionInfo = [];

        try {
            $versionInfo = $this->prepareVersionInfo($versionChecker);
        } catch (\Exception $e) {
            $versionInfo['error'] = 1;
            $this->logger->error('Version check error: ' . $e->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($versionInfo);
    }

    /**
     * @param VersionCheckerInterface $versionChecker
     * @return array
     */
    private function prepareVersionInfo($versionChecker)
    {
        $remoteVersion = $versionChecker->getRemoteVersion();

        return [
            'error' => 0,
            'version' => $remoteVersion,
            'isUpToDate' => $this->versionHelper->isSameAsCurrent($remoteVersion)
        ];
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Billink_Billink::resource');
    }
}