<?php

namespace Billink\Billink\Gateway\Config;

class BasePaymentConfig extends \Magento\Payment\Gateway\Config\Config
{
    const FIELD_ACTIVE = 'is_active';
    const FIELD_ACCOUNT_NAME = 'account_name';
    const FIELD_ACCOUNT_ID = 'account_id';
    const FIELD_DEBUG = 'debug';

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->getValue(self::FIELD_ACTIVE);
    }

    /**
     * @return string
     */
    public function getAccountName(): string
    {
        return $this->getValue(self::FIELD_ACCOUNT_NAME);
    }

    /**
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->getValue(self::FIELD_ACCOUNT_ID);
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return (bool)$this->getValue(self::FIELD_DEBUG);
    }

}
