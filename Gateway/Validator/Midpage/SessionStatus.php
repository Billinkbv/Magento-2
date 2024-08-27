<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

class SessionStatus extends AbstractCommon
{
    public const STATUS_EXPIRED = 'session_expired';
    public const STATUS_ACTIVE = 'session_active';
    public const STATUS_PAID = 'order_created';
}
