<?php

namespace Billink\Billink\Model\Payment;

use Billink\Billink\Api\MidpageResultDataInterface;

class MidpageResultData implements MidpageResultDataInterface
{
    private string $url;

    public function __construct(
        string $url
    ) {
        $this->url = $url;
    }

    public function getRedirectUrl(): string
    {
        return $this->url;
    }
}
