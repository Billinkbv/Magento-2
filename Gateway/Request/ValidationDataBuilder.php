<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

class ValidationDataBuilder implements BuilderInterface
{
    public const CHECKUUID = 'CHECKUUID';
    public const VALIDATEORDER = 'VALIDATEORDER';

    public function __construct(
        private readonly SubjectReader $subjectReader,
        private readonly WorkflowHelper $workflowHelper
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $type = $this->subjectReader->readPaymentWorkflowType($buildSubject);
        if (!$this->workflowHelper->getIsWithCheck($type)) {
            return [];
        }

        $validationFlag = $this->subjectReader->readValidationFlag($buildSubject);
        $checkUUID = $this->subjectReader->readPaymentCheckUUID($buildSubject);

        $result = [
            self::CHECKUUID => $checkUUID
        ];

        if ($validationFlag) {
            $result[self::VALIDATEORDER] = 'Y';
        }

        return $result;
    }
}
