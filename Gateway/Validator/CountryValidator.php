<?php

namespace Billink\Billink\Gateway\Validator;

use Billink\Billink\Gateway\Config\Config;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\AbstractValidator;

/**
 * Class CountryValidator
 * @package Billink\Billink\Gateway\Validator
 */
class CountryValidator extends AbstractValidator
{
    /**
     * @var Config
     */
    private $config;

    /**
     * CountryValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param Config $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Config $config
    ) {
        $this->config = $config;

        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return bool
     * @throws NotFoundException
     * @throws \Exception
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;

        if ((int)$this->config->getAllowSpecific()) {
            $availableCountries = explode(
                ',',
                $this->config->getSpecificCountry()
            );

            if (!in_array($validationSubject['country'], $availableCountries)) {
                $isValid = false;
            }
        }

        return $this->createResult($isValid);
    }
}