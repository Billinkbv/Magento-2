<?php

namespace Billink\Billink\Gateway\Helper;

/**
 * Class Address
 * @package Billink\Billink\Gateway\Helper
 */
class Address
{
    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $address1
     * @param \Magento\Quote\Api\Data\AddressInterface $address2
     * @return bool
     */
    public function areEqual($address1, $address2)
    {
        return $this->serializeAddress($address1) === $this->serializeAddress($address2);
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return string
     */
    public function serializeAddress($address)
    {
        return serialize([
            'firstname'     => (string)$address->getFirstname(),
            'lastname'      => (string)$address->getLastname(),
            'street'        => array_map('strval', $address->getStreet()),
            'company'       => (string)$address->getCompany(),
            'city'          => (string)$address->getCity(),
            'postcode'      => (string)$address->getPostcode(),
        ]);
    }
}