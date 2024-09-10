<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

class Refund extends AbstractCommon
{
    public const RESULT = 'result';
    public const STATUSES = 'statuses';
    protected array $desiredKeys = [
        self::RESULT,
        self::STATUSES,
    ];

    protected function getDesiredKeys(): array
    {
        return $this->desiredKeys;
    }
}
