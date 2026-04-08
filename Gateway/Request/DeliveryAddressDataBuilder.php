<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\Address as AddressHelper;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Framework\Session\SessionManager as Session;
use Magento\Payment\Gateway\Request\BuilderInterface;

class DeliveryAddressDataBuilder implements BuilderInterface
{
    public const DELIVERY_STREET = 'DELIVERYSTREET';
    public const DELIVERY_HOUSENUMBER = 'DELIVERYHOUSENUMBER';
    public const DELIVERY_HOUSEEXTENSION = 'DELIVERYHOUSEEXTENSION';
    public const DELIVERY_POSTALCODE = 'DELIVERYPOSTALCODE';
    public const DELIVERY_COUNTRYCODE = 'DELIVERYCOUNTRYCODE';
    public const DELIVERY_CITY = 'DELIVERYCITY';
    public const DELIVERY_COMPANYNAME = 'DELIVERYADDRESSCOMPANYNAME';
    public const DELIVERY_FIRSTNAME = 'DELIVERYADDRESSFIRSTNAME';
    public const DELIVERY_LASTNAME = 'DELIVERYADDRESSLASTNAME';

    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly Session $checkoutSession,
        private readonly AddressHelper $addressHelper
    ) {
    }

    public function build(array $buildSubject): array
    {
        $quote = $this->checkoutSession->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();

        $result = [];

        if ($shippingAddress && !$this->addressHelper->areEqual($billingAddress, $shippingAddress)) {
            $deliveryStreet = $this->subjectReader
                ->readPaymentAIField(DataAssignObserver::DELIVERY_ADDRESS_STREET, $buildSubject);
            $deliveryHouseNumber = $this->subjectReader
                ->readPaymentAIField(DataAssignObserver::DELIVERY_ADDRESS_HOUSENUMBER, $buildSubject);
            $deliveryHouseExtension = $this->subjectReader
                ->readPaymentAIField(DataAssignObserver::DELIVERY_ADDRESS_HOUSEEXTENSION, $buildSubject);

            $result = [
                self::DELIVERY_STREET => $deliveryStreet,
                self::DELIVERY_HOUSENUMBER => $deliveryHouseNumber,
                self::DELIVERY_HOUSEEXTENSION => $deliveryHouseExtension,
                self::DELIVERY_POSTALCODE => $shippingAddress->getPostcode(),
                self::DELIVERY_COUNTRYCODE => $shippingAddress->getCountryId(),
                self::DELIVERY_CITY => $shippingAddress->getCity(),
                self::DELIVERY_COMPANYNAME => $shippingAddress->getCompany(),
                self::DELIVERY_FIRSTNAME => $shippingAddress->getFirstname(),
                self::DELIVERY_LASTNAME => $shippingAddress->getLastname()
            ];
        }

        return $result;
    }
}
