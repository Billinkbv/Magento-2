<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class WorkflowDataBuilder
 * @package Billink\Billink\Gateway\Request
 */
class WorkflowDataBuilder implements BuilderInterface
{
    const TYPE = 'TYPE';
    const WORKFLOWNUMBER = 'WORKFLOWNUMBER';
    const BACKDOOR = 'BACKDOOR';

    public function __construct(
        protected readonly Config $config,
        protected readonly SubjectReader $subjectReader,
        protected readonly WorkflowHelper $workflowHelper
    ) {
    }

    /**
     * Builds ENV request
     */
    public function build(array $buildSubject): array
    {
        $type = $this->subjectReader->readPaymentWorkflowType($buildSubject);

        $result = [
            self::TYPE => $type,
            self::WORKFLOWNUMBER => $this->workflowHelper->getNumber($type)
        ];

        if ($this->config->isDebugMode()) {
            $result[self::BACKDOOR] = $this->config->getBackdoorOption();
        }

        return $result;
    }
}
