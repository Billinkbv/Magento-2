<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class ActionDataBuilder implements BuilderInterface
{
    const ACTION = 'ACTION';
    const SERVICE = 'SERVICE';
    const INVOICE_EMAIL = 'EMAIL2';

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $service;

    private SubjectReader $subjectReader;

    /**
     * ActionDataBuilder constructor.
     * @param string $action
     * @param string $service
     */
    public function __construct(string $action, string $service, SubjectReader $subjectReader)
    {
        $this->action = $action;
        $this->service = $service;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $result = [
            self::ACTION => $this->action,
            self::SERVICE => $this->service
        ];

        if (strtolower($this->action) == 'order') {
            $workflowType = $this->subjectReader->readPaymentWorkflowType($buildSubject);

            if (WorkflowHelper::TYPE_BUSINESS === $workflowType) {
               $invoiceEmail = $this->subjectReader->readPaymentAIField(DataAssignObserver::INVOICE_EMAIL, $buildSubject);
               if ($invoiceEmail) {
                   $result[self::INVOICE_EMAIL] = $invoiceEmail;
               }
            }
        }

        return $result;
    }
}
