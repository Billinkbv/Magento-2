<?php

namespace Billink\Billink\Gateway\Validator;

use Billink\Billink\Gateway\Config\Config;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

use function explode;
use function in_array;

class CountryValidator extends AbstractValidator
{
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        private readonly Config $config
    ) {
        parent::__construct($resultFactory);
    }

    public function validate(array $validationSubject): ResultInterface
    {
        $isValid = true;

        if ((int) $this->config->getAllowSpecific()) {
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
