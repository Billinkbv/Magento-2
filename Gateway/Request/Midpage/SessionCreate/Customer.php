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
    public function build(array $buildSubject): array
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

    private function getAddressData(?\Magento\Payment\Gateway\Data\AddressAdapterInterface $address): array
    {
        $street = $address->getStreetLine1();
        $parts = $this->getParts($street);
        if (!isset($parts['street'])) {
            $parts['street'] = $street;
        }
        if (!isset($parts['housenumber']) || !$parts['housenumber']) {
            // Use the street line-2 as a number
            $parts['housenumber'] = $address->getStreetLine2();
        }
        if (!$parts['housenumber']) {
            $parts['housenumber'] = '-';
        }
        return [
            'street' => $parts['street'],
            'houseNumber' => $parts['housenumber'],
            'houseExtension' => $parts['ext'] ?? '-',
            'postalCode' => $address->getPostcode(),
            'city' => $address->getCity(),
            'countryCode' => $address->getCountryId()
        ];
    }

    private function getParts(string $street): array
    {
        $regexp = '/^(?<street>\d*[\p{L}\d \'\/\-\.]+)[,\s]+(?<housenumber>\d+)\s*(?<ext>[\p{L} \d\-\/\'"\(\)]*)$/';
        preg_match($regexp, $street, $matches);
        return $matches;
    }
}
