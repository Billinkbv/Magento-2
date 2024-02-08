<?php

namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class Customer implements BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        /** @var OrderAdapter $order */
        $order = $paymentDO->getOrder();
        $address = $order->getBillingAddress();

        $customer = [
            'firstName' => $address->getFirstname(),
            'lastName' => $address->getLastname(),
            'email' => $address->getEmail(),
            'mobile' => $address->getTelephone(),

            'type' => 'p',
            'initials' => '',
            'gender' => '',
            'birthdate' => '',
            'company' => '',
            'companyNumber' => ''
		];

        return [
            'customer' => $customer,
            'billingAddress' => $this->getAddressData($address),
            'shippingAddress' => $this->getAddressData($order->getShippingAddress()),
        ];
    }

    private function getAddressData(?\Magento\Payment\Gateway\Data\AddressAdapterInterface $address)
    {
        $street = $address->getStreetLine1();
        $houseNumber = $address->getStreetLine2();
        if (!$houseNumber) {
            // Extract last part of the street line
            $street = explode(' ', trim($street));
            if (!isset($street[1])) {
                throw new LocalizedException(__('billink_order_error_code_414'));
            }
            $houseNumber = array_pop($street);
            $street = implode(' ', $street);
        }
        return [
            'street' => $street,
            'houseNumber' => $houseNumber,
            'houseExtension' => '',
            'postalCode' => $address->getPostcode(),
            'city' => $address->getCity(),
            'countryCode' => $address->getCountryId()
        ];
    }
}
