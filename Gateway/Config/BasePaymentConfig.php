<?php

namespace Billink\Billink\Gateway\Config;

class BasePaymentConfig extends \Magento\Payment\Gateway\Config\Config
{
    const FIELD_ACTIVE = 'is_active';
    const FIELD_ACCOUNT_NAME = 'account_name';
    const FIELD_ACCOUNT_ID = 'account_id';
    const FIELD_DEBUG = 'debug';

    const FIELD_IS_FEE_ACTIVE = 'is_fee_active';
    const FIELD_FEE_LABEL = 'fee_label';
    const FIELD_FEE_TYPE = 'fee_type';
    const FIELD_FEE_TAX_CLASS = 'fee_tax_class';
    const FIELD_FEE_RANGE = 'fee_range';

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

    public function getIsFeeActive(): bool
    {
        return (bool)$this->getValue(self::FIELD_IS_FEE_ACTIVE);
    }

    public function getFeeLabel(): string
    {
        return $this->getValue(self::FIELD_FEE_LABEL) ?: __('Billink Service Fee');
    }

    public function getFeeType(): string
    {
        return (string)$this->getValue(self::FIELD_FEE_TYPE);
    }

    public function getFeeTaxClass(): string
    {
        return (string)$this->getValue(self::FIELD_FEE_TAX_CLASS);
    }

    public function getFeeRange(): array
    {
        try {
            $result = json_decode(
                $this->getValue(self::FIELD_FEE_RANGE),
                true,
                32,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            $result = [];
        }
        return $result;
    }
}
