<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;

class SessionCreate extends AbstractCommon
{
    protected array $desiredKeys = [
        'status',
        SessionReader::REDIRECT_URL,
    ];

    protected function getDesiredKeys(): array
    {
        return $this->desiredKeys;
    }
}
