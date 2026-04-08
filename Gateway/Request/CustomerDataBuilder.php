<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Billink\Billink\Util\UserAgentParser;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CustomerDataBuilder implements BuilderInterface
{
    public const FIRSTNAME = 'FIRSTNAME';
    public const LASTNAME = 'LASTNAME';
    public const INITIALS = 'INITIALS';
    public const HOUSENUMBER = 'HOUSENUMBER';
    public const HOUSEEXTENSION = 'HOUSEEXTENSION';
    public const POSTALCODE = 'POSTALCODE';
    public const PHONENUMBER = 'PHONENUMBER';
    public const BIRTHDATE = 'BIRTHDATE';
    public const EMAIL = 'EMAIL';
    public const IP = 'IP';
    public const STREET = 'STREET';
    public const COUNTRYCODE = 'COUNTRYCODE';
    public const CITY = 'CITY';
    public const DEVICE = 'DEVICE';
    public const BROWSER = 'BROWSER';
    public const REFERENCE = 'ADITIONALTEXT';

    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly DateTime $dateTime,
        private readonly Header $headerService
    ) {
    }

    public function build(array $buildSubject): array
    {
        $payment = $this->subjectReader->readPayment($buildSubject);
        $workflowType = $this->subjectReader->readPaymentWorkflowType($buildSubject);

        $orderData = $payment->getQuote() ?: $payment->getOrder();
        $billingAddress = $orderData->getBillingAddress();

        $customerEmail = $orderData->getEmail() ?: ($billingAddress->getEmail() ?: false);
        $customerPhonenumber = $orderData->getTelephone() ?: ($billingAddress->getTelephone() ?: false);

        $headerData = UserAgentParser::parse_user_agent($this->headerService->getHttpUserAgent());

        $result = [
            self::EMAIL => $customerEmail,
            self::PHONENUMBER => $customerPhonenumber,
            self::FIRSTNAME => $billingAddress->getFirstname(),
            self::LASTNAME => $billingAddress->getLastname(),
            self::POSTALCODE => $billingAddress->getPostcode(),
            self::STREET => $this->subjectReader->readPaymentAIField(DataAssignObserver::STREET, $buildSubject),
            self::COUNTRYCODE => $billingAddress->getCountryId(),
            self::CITY => $billingAddress->getCity(),
            self::HOUSENUMBER =>
                $this->subjectReader->readPaymentAIField(DataAssignObserver::HOUSE_NUMBER, $buildSubject),
            self::HOUSEEXTENSION =>
                $this->subjectReader->readPaymentAIField(DataAssignObserver::HOUSE_EXTENSION, $buildSubject),
            self::IP => $orderData->getRemoteIp(),
            self::DEVICE => $headerData['platform'],
            self::BROWSER => $headerData['browser'] . ' ' . $headerData['version'],
            self::REFERENCE => $this->subjectReader->readPaymentAIField(DataAssignObserver::REFERENCE, $buildSubject)
        ];

        if ($workflowType === WorkflowHelper::TYPE_PRIVATE) {
            $birthDate = $this->subjectReader->readPaymentAIField(DataAssignObserver::BIRTHDATE, $buildSubject);

            $result[self::BIRTHDATE] = $this->dateTime->date('d-m-Y', $birthDate . ' 00:00:01');
        }

        return $result;
    }
}
