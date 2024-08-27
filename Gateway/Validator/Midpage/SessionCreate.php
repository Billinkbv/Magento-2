<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;

class SessionCreate extends AbstractCommon
{
    public const INVOICE = 'invoice';
    public const SESSION_ID = 'sessionID';

    protected array $desiredKeys = [
        self::STATUS,
        SessionReader::REDIRECT_URL,
        self::INVOICE,
        self::SESSION_ID,
    ];
}
