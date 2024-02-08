<?php
namespace Billink\Billink\Gateway\Validator\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Helper\SubjectReader;

abstract class AbstractCommon implements ValidatorInterface
{
    protected ResultInterfaceFactory $validationResult;
    private SessionReader $sessionReader;

    protected array $desiredKeys = [];

    public function __construct(
        ResultInterfaceFactory $resultInterfaceFactory,
        SessionReader $sessionReader
    ) {
        $this->validationResult = $resultInterfaceFactory;
        $this->sessionReader = $sessionReader;
    }

    /**
     * get error from request
     * @param array $response
     * @return string
     */
    protected function getError(array $response): string
    {
        if(isset($response['status'], $response['message']) && $response['status'] === 'error') {
            return $response['message'];
        }
        return '';
    }

    /**
     * get error from request
     * @param array $response
     * @return string
     */
    protected function getErrorMessage(array $response)
    {
        if(isset($response['error']['message'])) {
            return $response['error']['message'];
        }
        return '';
    }

    /**
     * @param array $validationSubject
     * @return bool|\Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
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
            return $this->validationResult->create($result);
        }
        $differences = array_diff_key($response,array_flip($this->desiredKeys));
        if($differences){
            $result = [
                'isValid' => true,
                'failsDescription' => ['Response does not match to desired schema.']
            ];
        }
        return $this->validationResult->create($result);
    }

    /**
     * @return array
     */
    abstract protected function getDesiredKeys(): array;
}
