<?php

namespace Billink\Billink\Model\Payment;

use Billink\Billink\Api\MidpagePaymentDataInterface;
use Billink\Billink\Api\MidpageResultDataInterface;
use Billink\Billink\Api\MidpageResultDataInterfaceFactory;
use Billink\Billink\Gateway\Helper\SessionReader;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class MidpagePaymentData implements MidpagePaymentDataInterface
{
    public function __construct(
        protected readonly PaymentInformationManagementInterface $paymentInformationManagement,
        protected readonly GuestPaymentInformationManagementInterface $guestPaymentInformationManagement,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly MidpageResultDataInterfaceFactory $resultObjectFactory,
        protected readonly Session $session
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function savePaymentInformationAndPlaceOrder(
        int $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ): MidpageResultDataInterface {
        $orderId = $this->paymentInformationManagement
            ->savePaymentInformationAndPlaceOrder($cartId, $paymentMethod, $billingAddress);
        $order = $this->orderRepository->get($orderId);
        $redirectUrl = $this->getRedirectUrl($order);
        $this->session->activatePaymentSession($order);
        return $this->resultObjectFactory->create([
            'url' => $redirectUrl,
        ]);
    }

    /**
     * @throws LocalizedException
     */
    public function saveGuestPaymentInformationAndPlaceOrder(
        string $cartId,
        string $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ): MidpageResultDataInterface {
        $orderId = $this->guestPaymentInformationManagement
            ->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMethod, $billingAddress);
        $order = $this->orderRepository->get($orderId);
        $redirectUrl = $this->getRedirectUrl($order);
        $this->session->activatePaymentSession($order);
        return $this->resultObjectFactory->create([
            'url' => $redirectUrl,
        ]);
    }

    /**
     * @throws LocalizedException
     */
    private function getRedirectUrl(OrderInterface $order): string
    {
        $payment = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        $url = $additionalInformation[SessionReader::REDIRECT_URL] ?? '';
        if (!$url) {
            throw new LocalizedException(__('Something went wrong, incorrect payment data.'));
        }
        return $url;
    }
}
