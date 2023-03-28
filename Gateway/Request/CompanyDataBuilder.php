<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class CompanyDataBuilder implements BuilderInterface
{
    const COMPANYNAME = 'COMPANYNAME';
    const CHAMBEROFCOMMERCE = 'CHAMBEROFCOMMERCE';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * CompanyDataBuilder constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
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
        $workflowType = $this->subjectReader->readPaymentWorkflowType($buildSubject);

        if (WorkflowHelper::TYPE_BUSINESS !== $workflowType) {
            return [];
        }

        return [
            self::COMPANYNAME => $this->subjectReader->readPaymentAIField(
                DataAssignObserver::COMPANY_NAME,
                $buildSubject
            ),
            self::CHAMBEROFCOMMERCE => $this->subjectReader->readPaymentAIField(
                DataAssignObserver::CHAMBER_OF_COMMERCE,
                $buildSubject
            ),

        ];
    }
}
