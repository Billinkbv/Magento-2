<?php
namespace Billink\Billink\Gateway\Validator\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Helper\SubjectReader;

abstract class AbstractCommon implements ValidatorInterface
{
    public const STATUS = 'status';
    public const STATUS_SUCCESS = 'success';

    protected array $desiredKeys = [];

    public function __construct(
        protected readonly ResultInterfaceFactory $resultInterfaceFactory,
        protected readonly SessionReader $sessionReader
    ) {
    }

    /**
     * get error from request
     */
    protected function getError(array $response): string
    {
        if (isset($response['status'], $response['message']) && $response['status'] === 'error') {
            return $response['message'];
        }
        return '';
    }

    /**
     * get error from request
     */
    protected function getErrorMessage(array $response): string
    {
        if (isset($response['error']['message'])) {
            return $response['error']['message'];
        }
        return '';
    }

    public function validate(array $validationSubject): ResultInterface
    {
        $result = [
            'isValid' => true,
            'failsDescription' => []
        ];
        $responseObject = SubjectReader::readResponse($validationSubject);
        $response = $this->sessionReader->getResponse($responseObject);
        if ($errorMessage = $this->getError($response)) {
            $result = [
                'isValid' => false,
                'failsDescription' => [
                    $errorMessage
                ]
            ];
            return $this->resultInterfaceFactory->create($result);
        }
        // Validate that all keys exists in response.
        $differences = array_diff_key(array_flip($this->desiredKeys), $response);
        if ($differences) {
            $result = [
                'isValid' => false,
                'failsDescription' => ['Response does not match to desired schema.']
            ];
        }
        return $this->resultInterfaceFactory->create($result);
    }

    abstract protected function getDesiredKeys(): array;
}
