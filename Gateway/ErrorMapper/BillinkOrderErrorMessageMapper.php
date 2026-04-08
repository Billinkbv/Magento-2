<?php

namespace Billink\Billink\Gateway\ErrorMapper;

use Magento\Framework\Phrase;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;

use function __;

class BillinkOrderErrorMessageMapper implements ErrorMessageMapperInterface
{
    public function getMessage(string $code): ?Phrase
    {
        return __('Could not process billink order: %1', $code);
    }
}
