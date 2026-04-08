<?php

namespace Billink\Billink\Controller\Adminhtml\Config;

use Billink\Billink\Gateway\Command\MidpageGatewayCommand;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class Sync extends Action
{
    public function __construct(
        Action\Context $context,
        private readonly MidpageGatewayCommand $command,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        $result = [
            'message' => 'Success!'
        ];

        try {
            $this->command->execute([]);
        } catch (Exception $e) {
            $result['error'] = 1;
            $result['message'] = 'Failed. Error: ' . $e->getMessage();
            $this->logger->error('Error: ' . $e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Billink_Billink::resource');
    }
}
