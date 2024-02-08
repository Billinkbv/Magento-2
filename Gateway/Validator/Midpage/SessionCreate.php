<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

use Billink\Billink\Gateway\Helper\SessionReader;

class SessionCreate extends AbstractCommon
{
    /**
     * @var array
     */
    protected array $desiredKeys = [
        'status',
        SessionReader::REDIRECT_URL,
    ];

    /**
     * @return array
     */
    protected function getDesiredKeys(): array
    {
        return $this->desiredKeys;
    }
}
