<?php

namespace Billink\Billink\Block\Adminhtml\Order\Create\Billing\Method;

use Billink\Billink\Gateway\Helper\Workflow;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

class Form extends \Magento\Payment\Block\Form
{
    public function __construct(
        Template\Context $context,
        private readonly Workflow $workflowHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getCustomerTypes(): array
    {
        return $this->workflowHelper->getTypes();
    }

    /**
     * Override so default values get set in the list
     *
     * @param string $field
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getInfoData($field)
    {
        $instance = $this->getMethod()->getInfoInstance();
        if (!$instance->hasData($field)) {
            /** @var Customer $customer */
            $customer = $instance->getQuote()->getCustomer();

            if ($field === $this->getMethodCode() . '_customer_birthdate') {
                $dob = $customer->getDob();
                if ($dob !== null) {
                    $instance->setData($field, $dob);
                }
            }
        }

        return parent::getInfoData($field);
    }
}
