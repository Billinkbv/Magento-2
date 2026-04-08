<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Exception;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

use function __;
use function array_diff_key;
use function array_flip;
use function is_array;
use function is_string;
use function json_decode;

abstract class AbstractCommon implements ValidatorInterface
{
    public const STATUS = 'status';
    public const STATUS_SUCCESS = 'success';

    protected array $desiredKeys = [
        self::STATUS
    ];

    public function __construct(
        protected readonly ResultInterfaceFactory $resultInterfaceFactory,
        protected readonly SessionReader $sessionReader
    ) {
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
        if (array_diff_key(array_flip($this->desiredKeys), $response)) {
            $result = [
                'isValid' => false,
                'failsDescription' => [
                    __(
                        'Something went wrong with creating session. Please contact customer support of Billink.'
                    )
                ]
            ];
        }

        return $this->resultInterfaceFactory->create($result);
    }

    /**
     * get error from request
     */
    protected function getError(array $response): string
    {
        if (isset($response['status'], $response['message']) && $response['status'] === 'error') {
            return $response['message'];
        }
        if (isset($response['error_backend']['http_body'])) {
            if (is_array($response['error_backend']['http_body'])) {
                return 'Billink error: ' . $response['error_backend']['http_body']['message'];
            }
            try {
                // Try unpacking error
                if (is_string($response['error_backend']['http_body'])) {
                    $error = json_decode($response['error_backend']['http_body'], true);

                    return 'Billink error: ' . $error['message'];
                }
            } catch (Exception $e) {
                // Incorrect format, return full string.
                return $response['error_backend']['http_body'];
            }

            return $response['error_backend'];
        }

        return '';
    }

    /**
     * get error from request
     */
    protected function getErrorMessage(array $response): string
    {
        return $response['error']['message'] ?? '';
    }

    protected function getDesiredKeys(): array
    {
        return $this->desiredKeys;
    }
}
