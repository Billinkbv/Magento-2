<?php

namespace Billink\Billink\Model\Payment;

use Billink\Billink\Api\MidpageResultDataInterface;

class MidpageResultData implements MidpageResultDataInterface
{
    public function __construct(
        private readonly string $url
    ) {
    }

    public function getRedirectUrl(): string
    {
        return $this->url;
    }
}
