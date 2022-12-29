<?php
namespace Billink\Billink\Gateway\ErrorMapper;

use Magento\Framework\Phrase;

class BillinkOrderErrorMessageMapper implements \Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface
{
    public function getMessage(string $code)
    {
        return __("Could not process billink order: %1", $code);
    }
}
