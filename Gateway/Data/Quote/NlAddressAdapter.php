<?php

namespace Billink\Billink\Gateway\Data\Quote;

use Magento\Quote\Api\Data\AddressInterface;

class NlAddressAdapter extends \Magento\Payment\Gateway\Data\Quote\AddressAdapter
{
    public function __construct(
        private readonly AddressInterface $address
    ) {
        parent::__construct($this->address);
    }

    /**
     * Get street line 3 - House extension
     *
     * @return string
     */
    public function getStreetLine3(): string
    {
        $street = $this->address->getStreet();
        return $street[2] ?? '';
    }


}
