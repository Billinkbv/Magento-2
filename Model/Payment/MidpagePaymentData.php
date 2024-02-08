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
    private PaymentInformationManagementInterface $paymentInformationManagement;
    private GuestPaymentInformationManagementInterface $guestPaymentInformationManagement;
    private OrderRepositoryInterface $orderRepository;
    private MidpageResultDataInterfaceFactory $resultObjectFactory;
    private Session $session;

    public function __construct(
        PaymentInformationManagementInterface $informationManagement,
        GuestPaymentInformationManagementInterface $guestPaymentInformationManagement,
        OrderRepositoryInterface $orderRepository,
        MidpageResultDataInterfaceFactory $resultObjectFactory,
        Session $session
    ) {
        $this->paymentInformationManagement = $informationManagement;
        $this->guestPaymentInformationManagement = $guestPaymentInformationManagement;
        $this->orderRepository = $orderRepository;
        $this->resultObjectFactory = $resultObjectFactory;
        $this->session = $session;
    }

    /**
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return MidpageResultDataInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
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
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return MidpageResultDataInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveGuestPaymentInformationAndPlaceOrder(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $orderId = $this->guestPaymentInformationManagement
            ->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMethod, $billingAddress);
        $order = $this->orderRepository->get($orderId);
        $redirectUrl = $this->getRedirectUrl($order);
        $this->session->activatePaymentSession($order);
        return $this->resultObjectFactory->create([
            'url' => $redirectUrl,
        ]);
    }

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
