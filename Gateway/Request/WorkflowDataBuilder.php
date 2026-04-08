<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

class WorkflowDataBuilder implements BuilderInterface
{
    public const TYPE = 'TYPE';
    public const WORKFLOWNUMBER = 'WORKFLOWNUMBER';
    public const BACKDOOR = 'BACKDOOR';

    public function __construct(
        private readonly Config $config,
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
