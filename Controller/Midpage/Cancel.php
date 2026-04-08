<?php

namespace Billink\Billink\Controller\Midpage;

use Exception;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;

use function __;

class Cancel extends AbstractAction
{
    public function execute(): Redirect
    {
        $resultRedirect = $this->redirectFactory->create();

        $transactionOrderId = $this->getOrderIdFromTransaction();
        if ($transactionOrderId === null) {
            $resultRedirect->setPath('checkout/cart');

            return $resultRedirect;
        }

        $params = ['_secure' => true];
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            if ((string) $order->getIncrementId() !== $transactionOrderId) {
                $order = $this->loadOrderByIncrementId($transactionOrderId);
            }
            $this->paymentSession->deactivatePaymentSessionById($order->getEntityId());
            $paymentDO = [
                'payment' => $this->paymentDataObjectFactory->create($order->getPayment())
            ];
            $command = $this->commandPool->get('order_cancel');
            $command->execute($paymentDO);
        } catch (LocalizedException $e) {
            $this->logger->notice($e->getMessage());
            $this->messageManager->addExceptionMessage($e);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('There was an error during your request.'));
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return $resultRedirect->setPath('checkout/cart', $params);
    }
}
