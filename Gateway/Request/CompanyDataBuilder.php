<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CompanyDataBuilder implements BuilderInterface
{
    public const COMPANYNAME = 'COMPANYNAME';
    public const CHAMBEROFCOMMERCE = 'CHAMBEROFCOMMERCE';

    public function __construct(
        private readonly SubjectReader $subjectReader
    ) {
    }

    public function build(array $buildSubject): array
    {
        $workflowType = $this->subjectReader->readPaymentWorkflowType($buildSubject);

        if ($workflowType !== WorkflowHelper::TYPE_BUSINESS) {
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
