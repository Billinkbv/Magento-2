<?php
namespace Billink\Billink\Controller\Adminhtml\Config;

use Billink\Billink\Gateway\Command\MidpageGatewayCommand;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class Sync extends Action
{
    public function __construct(
        Action\Context $context,
        private readonly JsonFactory $resultJsonFactory,
        private readonly MidpageGatewayCommand $command,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [
            'message' => 'Success!'
        ];

        try {
            $this->command->execute([]);
        } catch (\Exception $e) {
            $result['error'] = 1;
            $result['message'] = 'Failed. Error: ' . $e->getMessage();
            $this->logger->error('Error: ' . $e->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Billink_Billink::resource');
    }
}
