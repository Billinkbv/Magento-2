<?php

namespace Billink\Billink\Gateway\Request\Midpage\SessionCreate;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Billink\Billink\Gateway\Data\Quote\NlAddressAdapter;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Data\Quote\AddressAdapterFactory;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class Customer implements BuilderInterface
{
    public function __construct(
        private readonly Session $session,
        private readonly AddressAdapterFactory $addressAdapterFactory,
        private readonly MidpageConfig $config
    ) {
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        /** @var OrderAdapterInterface $orderAdapter */
        $orderAdapter = $paymentDO->getOrder();
        $address = $orderAdapter->getBillingAddress();
        $shippingAddress = $this->getShippingAddress($paymentDO);
        // In case of downloadable products shipping is not applied.
        if (!$shippingAddress) {
            $shippingAddress = $address;
        }
        if (!$address || !$shippingAddress) {
            throw new LocalizedException(__('The customer address is not valid.'));
        }
        $company = trim((string)$address->getCompany());

        $customer = [
            'firstName' => $address->getFirstname(),
            'lastName' => $address->getLastname(),
            'email' => $address->getEmail(),
            'mobile' => $address->getTelephone(),
            // Switch between Private/Business
            'type' => $company ? 'b' : 'p',
            'initials' => '',
            'gender' => '',
            'birthdate' => '',
            'company' => $company,
            'companyNumber' => '',
            'payTrustScore' => (int)$this->config->getValue('trust_score')
		];

        return [
            'customer' => $customer,
            'billingAddress' => $this->getAddressData($address),
            'shippingAddress' => $this->getAddressData($shippingAddress),
        ];
    }

    private function getAddressData(AddressAdapterInterface $address): array
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
            $parts['housenumber'] = '';
        }
        if ((!isset($parts['ext']) || !$parts['ext']) && $address instanceof NlAddressAdapter) {
            // Use the street line-3 as a house extension
            $parts['ext'] = $address->getStreetLine3();
        }
        return [
            'street' => $parts['street'],
            'houseNumber' => $parts['housenumber'],
            'houseExtension' => $parts['ext'] ?? '',
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

    private function getShippingAddress(PaymentDataObjectInterface $paymentDO): ?AddressAdapterInterface
    {
        $order = false;
        // Try to extract order from the payment
        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        if (method_exists($payment, 'getOrder')) {
            $order = $payment->getOrder();
        }

        // Get current quote and quote addresses
        $quote = $this->session->getQuote();
        if ($quote && $order && str_contains($order->getData('shipping_method'), 'tig_postnl')) {
            // Check any quote address to be set to postnl delivery
            $addresses = array_filter($quote->getAllAddresses(), function ($address) {
                return $address->getAddressType() === 'pakjegemak';
            });
            if (!empty($addresses)) {
                return $this->addressAdapterFactory->create(
                    ['address' => array_pop($addresses)]
                );
            }
        }
        // Use the default one
        return $paymentDO->getOrder()->getShippingAddress();
    }
}
