<?php
namespace Billink\Billink\Controller\Midpage;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class Place extends AbstractAction
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();

        $transactionOrderId = $this->getOrderIdFromTransaction();
        if ($transactionOrderId === null) {
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;
        }

        $params = ['_secure' => true];
        $order = $this->checkoutSession->getLastRealOrder();
        try {
            if ($order->getIncrementId() === $transactionOrderId) {
                // Normal flow - customer returns on alive session
                $this->paymentSession->deactivatePaymentSession();
                $this->executeOrderUpdate($order);
            } else {
                // Updated flow - session is dead, but the order transaction id is correct one.
                // As transaction is correct - we should confirm order and redirect customer to the correct page.
                $order = $this->loadOrderByIncrementId($transactionOrderId);
                $this->paymentSession->deactivatePaymentSessionById($order->getEntityId());
                $this->executeOrderUpdate($order);
                $this->checkoutSession
                    // Set up quote data - \Magento\Checkout\Model\Session\SuccessValidator
                    ->setLastSuccessQuoteId($order->getQuoteId())
                    ->setLastQuoteId($order->getQuoteId())
                    // Set up order data
                    ->setLastOrderId($order->getEntityId())
                    ->setLastRealOrderId($order->getIncrementId());
            }
        } catch (LocalizedException $e) {
            $this->logger->notice($e->getMessage());
            $this->messageManager->addExceptionMessage($e);
            $resultRedirect->setPath('checkout/cart', $params);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was an error during your request.'));
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $resultRedirect->setPath('checkout/cart', $params);
            return $resultRedirect;
        }
        $resultRedirect->setPath('checkout/onepage/success', $params);
        return $resultRedirect;
    }

    private function executeOrderUpdate(\Magento\Sales\Model\Order $order)
    {
        $command = $this->commandPool->get('order_update');
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = [
            'payment' => $this->paymentDataObjectFactory->create($order->getPayment())
        ];
        $command->execute($paymentDO);
    }
}
