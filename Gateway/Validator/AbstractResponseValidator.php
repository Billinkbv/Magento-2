<?php

namespace Billink\Billink\Gateway\Validator;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Billink\Billink\Gateway\Exception\ResponseException;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Model\Billink\Response\ResponseInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class AbstractResponseValidator
 * @package Billink\Billink\Gateway\Validator
 */
abstract class AbstractResponseValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * AbstractResponseValidator constructor.
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;

        parent::__construct($resultFactory);
    }

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     * @throws ResponseException
     * @throws \Exception
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        try {
            if (!$response || !$response->hasData()) {
                throw new InvalidResponseException('Could not retrieve any data from Billink service');
            }

            foreach ($this->getResponseValidators() as $validator) {
                $validationResult = $validator($response);

                if (!$validationResult['result']) {
                    throw new ResponseException($validationResult['code'], $this->getService(), $validationResult['message'] ?? '');
                }
            }
        } catch (InvalidResponseException $e) {
            return $this->createResult(false, [$e->getMessage()]);
        }

        return $this->createResult(true);
    }

    /**
     * @return array
     */
    public function getResponseValidators()
    {
        return [
            function ($response) {
                if (!$response instanceof ResponseInterface) {
                    throw new InvalidResponseException('Invalid response interface');
                }

                if ($response->hasError()) {
                    return ['result' => false, 'code' => $response->getErrorCode(), 'message' => $response->getErrorDescription()];
                }

                return ['result' => true];
            }
        ];
    }

    /**
     * @return string
     */
    protected function getService()
    {
        return $this->service;
    }
}
