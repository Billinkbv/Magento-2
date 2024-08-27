<?php

namespace Billink\Billink\Gateway\Validator\Midpage;

class SyncConfig extends AbstractCommon
{
    public const MIN_PAID_ORDERS = 'minimum_paid_orders';
    public const PERIOD = 'period';
    public const TOTAL_AMOUNT = 'total_amount_higher_than';
    public const WHITELIST = 'whitelist';

    protected array $desiredKeys = [
        self::STATUS,
        self::MIN_PAID_ORDERS,
        self::PERIOD,
        self::TOTAL_AMOUNT,
        self::WHITELIST
    ];
}
