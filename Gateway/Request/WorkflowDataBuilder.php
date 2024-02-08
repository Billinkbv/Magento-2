<?php

namespace Billink\Billink\Gateway\Request;

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

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Workflow
     */
    private $workflowHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * WorkflowDataBuilder constructor.
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param WorkflowHelper $workflowHelper
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader,
        WorkflowHelper $workflowHelper
    ) {

        $this->subjectReader = $subjectReader;
        $this->workflowHelper = $workflowHelper;
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
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
