<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;

class SessionCreate extends AbstractCommon
{
    public const INVOICE = 'invoice';

    protected array $desiredKeys = [
        self::STATUS,
        SessionReader::REDIRECT_URL,
        self::INVOICE,
    ];

    protected function getDesiredKeys(): array
    {
        return $this->desiredKeys;
    }
}
