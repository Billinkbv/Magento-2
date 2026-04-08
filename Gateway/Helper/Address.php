<?php

namespace Billink\Billink\Gateway\Helper;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\AddressInterface;

use function array_map;

class Address
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    public function areEqual(AddressInterface $address1, AddressInterface $address2): bool
    {
        return $this->serializeAddress($address1) === $this->serializeAddress($address2);
    }

    public function serializeAddress(AddressInterface $address): string
    {
        return $this->serializer->serialize(
            [
                'firstname' => (string) $address->getFirstname(),
                'lastname' => (string) $address->getLastname(),
                'street' => array_map('\strval', $address->getStreet()),
                'company' => (string) $address->getCompany(),
                'city' => (string) $address->getCity(),
                'postcode' => (string) $address->getPostcode(),
            ]
        );
    }
}
