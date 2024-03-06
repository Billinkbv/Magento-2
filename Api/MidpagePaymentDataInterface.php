<?php
namespace Billink\Billink\Api;

interface MidpagePaymentDataInterface
{
    /**
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return \Billink\Billink\Api\MidpageResultDataInterface $object
     */
    public function savePaymentInformationAndPlaceOrder(
        int $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ): MidpageResultDataInterface;

    /**
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     *
     * @return \Billink\Billink\Api\MidpageResultDataInterface $object
     */
    public function saveGuestPaymentInformationAndPlaceOrder(
        string $cartId,
        string $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ): MidpageResultDataInterface;
}
