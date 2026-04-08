<?php

namespace Billink\Billink\Gateway\Validator;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Billink\Billink\Gateway\Exception\ResponseException;
use Billink\Billink\Gateway\Helper\SubjectReader;
use Billink\Billink\Model\Billink\Response\ResponseInterface;
use Exception;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

abstract class AbstractResponseValidator extends AbstractValidator
{
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        private readonly SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
    }

    /**
     * Performs domain-related validation for business object
     *
     * @throws ResponseException
     * @throws Exception
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        try {
            if (!$response || !$response->hasData()) {
                throw new InvalidResponseException('Could not retrieve any data from Billink service');
            }

            foreach ($this->getResponseValidators() as $validator) {
                $validationResult = $validator($response);

                if (!$validationResult['result']) {
                    throw new ResponseException(
                        $validationResult['code'],
                        $this->getService(),
                        $validationResult['message'] ?? ''
                    );
                }
            }
        } catch (InvalidResponseException $e) {
            return $this->createResult(false, [$e->getMessage()]);
        }

        return $this->createResult(true);
    }

    public function getResponseValidators(): array
    {
        return [
            function ($response) {
                if (!$response instanceof ResponseInterface) {
                    throw new InvalidResponseException('Invalid response interface');
                }

                if ($response->hasError()) {
                    return [
                        'result' => false,
                        'code' => $response->getErrorCode(),
                        'message' => $response->getErrorDescription()
                    ];
                }

                return ['result' => true];
            }
        ];
    }

    protected function getService(): string
    {
        return $this->service;
    }
}
