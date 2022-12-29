<?php

namespace Billink\Billink\Observer;

use Billink\Billink\Gateway\Helper\Workflow;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class DataAssignObserver
 * @package Billink\Billink\Observer
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const WORKFLOW_NUMBER = 'billink_workflow_number';

    const CUSTOMER_TYPE = 'billink_customer_type';
    const COMPANY_NAME = 'billink_company';
    const CHAMBER_OF_COMMERCE = 'billink_chamber_of_commerce';
    const HOUSE_NUMBER = 'billink_house_number';
    const HOUSE_EXTENSION = 'billink_house_extension';
    const STREET = 'billink_street';
    const BIRTHDATE = 'billink_customer_birthdate';
    const SEX = 'billink_customer_sex';
    const REFERENCE = 'billink_reference';

    // Delivery Address
    const DELIVERY_ADDRESS_STREET = 'billink_delivery_address_street';
    const DELIVERY_ADDRESS_HOUSENUMBER = 'billink_delivery_address_housenumber';
    const DELIVERY_ADDRESS_HOUSEEXTENSION = 'billink_delivery_address_housenumber_extension';

    const VALIDATE_ORDER_FLAG = 'billink_validate_order';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::CUSTOMER_TYPE,
        self::COMPANY_NAME,
        self::CHAMBER_OF_COMMERCE,
        self::HOUSE_NUMBER,
        self::HOUSE_EXTENSION,
        self::STREET,
        self::BIRTHDATE,
        self::SEX,
        self::DELIVERY_ADDRESS_STREET,
        self::DELIVERY_ADDRESS_HOUSENUMBER,
        self::DELIVERY_ADDRESS_HOUSEEXTENSION,
        self::VALIDATE_ORDER_FLAG,
        self::REFERENCE
    ];

    /**
     * @var Workflow
     */
    private $workflowHelper;

    /**
     * DataAssignObserver constructor.
     * @param Workflow $workflowHelper
     */
    public function __construct(
        Workflow $workflowHelper
    ) {
        $this->workflowHelper = $workflowHelper;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData) || empty($additionalData[self::CUSTOMER_TYPE])) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        $needRecalculate = false;
        if (isset($additionalData[self::CUSTOMER_TYPE]) &&
            $paymentInfo->getAdditionalInformation(self::CUSTOMER_TYPE) != $additionalData[self::CUSTOMER_TYPE]) {
            $needRecalculate = true;
        }

        $paymentInfo->setAdditionalInformation(
            self::WORKFLOW_NUMBER,
            $this->workflowHelper->getNumber($additionalData[self::CUSTOMER_TYPE])
        );

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
        //if customer type changed we need to recalculate totals to apply proper billink fee
        if ($needRecalculate) {
            $paymentInfo->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        }
    }
}
