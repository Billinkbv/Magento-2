<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Billink\Billink\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

use function strtolower;

class ActionDataBuilder implements BuilderInterface
{
    public const ACTION = 'ACTION';
    public const SERVICE = 'SERVICE';
    public const INVOICE_EMAIL = 'EMAIL2';

    public function __construct(
        private readonly string $action,
        private readonly string $service,
        private readonly SubjectReader $subjectReader
    ) {
    }

    /**
     * Builds ENV request
     */
    public function build(array $buildSubject): array
    {
        $result = [
            self::ACTION => $this->action,
            self::SERVICE => $this->service
        ];

        if (strtolower($this->action) === 'order') {
            $workflowType = $this->subjectReader->readPaymentWorkflowType($buildSubject);

            if ($workflowType === WorkflowHelper::TYPE_BUSINESS) {
                $invoiceEmail = $this->subjectReader->readPaymentAIField(
                    DataAssignObserver::INVOICE_EMAIL,
                    $buildSubject
                );
                if ($invoiceEmail) {
                    $result[self::INVOICE_EMAIL] = $invoiceEmail;
                }
            }
        }

        return $result;
    }
}
