<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

class StartWorkflow extends AbstractCommon
{
    public const STATUSES = 'statuses';

    protected array $desiredKeys = [
        self::STATUSES,
    ];
}
