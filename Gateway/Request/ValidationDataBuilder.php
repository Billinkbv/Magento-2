<?php

namespace Billink\Billink\Gateway\Request;

use Billink\Billink\Gateway\Config\Config;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Gateway\Helper\Workflow as WorkflowHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class ValidationDataBuilder implements BuilderInterface
{
    const CHECKUUID = 'CHECKUUID';
    const VALIDATEORDER = 'VALIDATEORDER';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Workflow
     */
    private $workflowHelper;

    /**
     * ValidationDataBuilder constructor.
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
        $this->config = $config;
        $this->workflowHelper = $workflowHelper;
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
        $isWithCheck = $this->workflowHelper->getIsWithCheck($type);

        if (!$isWithCheck) {
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