<?php
namespace Billink\Billink\Controller\Midpage;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class Cancel extends AbstractAction
{
    /**
     * @return
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();

        $transactionOrder = (string)$this->getOrderFromTransaction();
        if (!$transactionOrder) {
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;
        }

        $params = ['_secure' => true];
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            if ((string)$order->getIncrementId() !== $transactionOrder) {
                $order = $this->loadOrderByIncrementId($transactionOrder);
            }
            $this->paymentSession->deactivatePaymentSessionById($order->getEntityId());
            /** @var PaymentDataObjectInterface $paymentDO */
            $paymentDO = [
                'payment' => $this->paymentDataObjectFactory->create($order->getPayment())
            ];
            $command = $this->commandPool->get('order_cancel');
            $command->execute($paymentDO);
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was an error during your request.'));
            $this->logger->critical($e);
        }
        return $resultRedirect->setPath('checkout/cart', $params);
    }
}
