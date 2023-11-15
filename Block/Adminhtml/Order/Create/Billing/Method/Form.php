<?php

namespace Billink\Billink\Block\Adminhtml\Order\Create\Billing\Method;

use Magento\Customer\Model\Customer;
use Magento\Framework\View\Element\Template;

class Form extends \Magento\Payment\Block\Form
{
    /**
     * @var \Billink\Billink\Gateway\Helper\Workflow
     */
    private $workflowHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    public $jsonSerializer;

    public function __construct(
        Template\Context $context,
        \Billink\Billink\Gateway\Helper\Workflow $workflowHelper,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->workflowHelper = $workflowHelper;
        $this->jsonSerializer = $jsonSerializer;
    }


    public function getCustomerTypes()
    {
        return $this->workflowHelper->getTypes();
    }

    /**
     * Override so default values get set in the list
     *
     * @param string $field
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInfoData($field)
    {
        $instance = $this->getMethod()->getInfoInstance();
        if (!$instance->hasData($field)) {
            $quote = $instance->getQuote();
            /** @var Customer $customer */
            $customer = $quote->getCustomer();

            if ($field === $this->getMethodCode() . "_customer_birthdate") {
                $dob = $customer->getDob();
                if ($dob !== null) {
                    $instance->setData($field, $dob);
                }
            }
        }

        return parent::getInfoData($field);
    }

}
