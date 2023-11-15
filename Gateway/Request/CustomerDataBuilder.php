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
    const FIRSTNAME = 'FIRSTNAME';
    const LASTNAME = 'LASTNAME';
    const INITIALS = 'INITIALS';
    const HOUSENUMBER = 'HOUSENUMBER';
    const HOUSEEXTENSION = 'HOUSEEXTENSION';
    const POSTALCODE = 'POSTALCODE';
    const PHONENUMBER = 'PHONENUMBER';
    const BIRTHDATE = 'BIRTHDATE';
    const EMAIL = 'EMAIL';
    const IP = 'IP';
    const STREET = 'STREET';
    const COUNTRYCODE = 'COUNTRYCODE';
    const CITY = 'CITY';
    const DEVICE = 'DEVICE';
    const BROWSER = 'BROWSER';
    const REFERENCE = 'ADITIONALTEXT';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Header
     */
    private $headerService;

    /**
     * CustomerDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param DateTime $dateTime
     * @param Header $headerService
     */
    public function __construct(
        SubjectReader $subjectReader,
        DateTime $dateTime,
        Header $headerService
    ) {
        $this->subjectReader = $subjectReader;
        $this->dateTime = $dateTime;
        $this->headerService = $headerService;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
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

        if (WorkflowHelper::TYPE_PRIVATE === $workflowType) {
            $birthDate = $this->subjectReader->readPaymentAIField(DataAssignObserver::BIRTHDATE, $buildSubject);

            $result = array_merge($result, [
                self::BIRTHDATE => $this->dateTime->date('d-m-Y', $birthDate .' 00:00:01')
            ]);
        }

        return $result;
    }
}
