<?php

namespace Billink\Billink\Gateway\Helper;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class Address
 * @package Billink\Billink\Gateway\Helper
 */
class Address
{
    /** @var SerializerInterface */
    private $serializer;

    /**
     * Address constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param AddressInterface $address1
     * @param AddressInterface $address2
     * @return bool
     */
    public function areEqual(AddressInterface $address1, AddressInterface $address2)
    {
        return $this->serializeAddress($address1) === $this->serializeAddress($address2);
    }

    /**
     * @param AddressInterface $address
     * @return string
     */
    public function serializeAddress(AddressInterface $address)
    {
        return $this->serializer->serialize([
            'firstname'     => (string)$address->getFirstname(),
            'lastname'      => (string)$address->getLastname(),
            'street'        => array_map('strval', $address->getStreet()),
            'company'       => (string)$address->getCompany(),
            'city'          => (string)$address->getCity(),
            'postcode'      => (string)$address->getPostcode(),
        ]);
    }
}