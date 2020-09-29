<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\Address as AddressHelper;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Framework\Session\SessionManager as Session;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class DeliveryAddressDataBuilder implements BuilderInterface
{
    const DELIVERY_STREET = 'DELIVERYSTREET';
    const DELIVERY_HOUSENUMBER = 'DELIVERYHOUSENUMBER';
    const DELIVERY_HOUSEEXTENSION = 'DELIVERYHOUSEEXTENSION';
    const DELIVERY_POSTALCODE = 'DELIVERYPOSTALCODE';
    const DELIVERY_COUNTRYCODE = 'DELIVERYCOUNTRYCODE';
    const DELIVERY_CITY = 'DELIVERYCITY';
    const DELIVERY_COMPANYNAME = 'DELIVERYADDRESSCOMPANYNAME';
    const DELIVERY_FIRSTNAME = 'DELIVERYADDRESSFIRSTNAME';
    const DELIVERY_LASTNAME = 'DELIVERYADDRESSLASTNAME';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * DeliveryAddressDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param Session $checkoutSession
     * @param AddressHelper $addressHelper
     */
    public function __construct(
        SubjectReader $subjectReader,
        Session $checkoutSession,
        AddressHelper $addressHelper
    ) {
        $this->subjectReader = $subjectReader;
        $this->checkoutSession = $checkoutSession;
        $this->addressHelper = $addressHelper;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
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