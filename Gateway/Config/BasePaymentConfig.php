<?php

namespace Billink\Billink\Gateway\Config;

use JsonException;

use function __;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class BasePaymentConfig extends \Magento\Payment\Gateway\Config\Config
{
    public const FIELD_ACTIVE = 'is_active';
    public const FIELD_ACCOUNT_NAME = 'account_name';
    public const FIELD_ACCOUNT_ID = 'account_id';
    public const FIELD_DEBUG = 'debug';

    public const FIELD_IS_FEE_ACTIVE = 'is_fee_active';
    public const FIELD_FEE_LABEL = 'fee_label';
    public const FIELD_FEE_TYPE = 'fee_type';
    public const FIELD_FEE_TAX_CLASS = 'fee_tax_class';
    public const FIELD_FEE_RANGE = 'fee_range';

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->getValue(self::FIELD_ACTIVE);
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
        return (bool) $this->getValue(self::FIELD_DEBUG);
    }

    public function getIsFeeActive(): bool
    {
        return (bool) $this->getValue(self::FIELD_IS_FEE_ACTIVE);
    }

    public function getFeeLabel(): string
    {
        return $this->getValue(self::FIELD_FEE_LABEL) ?: __('Billink Service Fee')->render();
    }

    public function getFeeType(): string
    {
        return (string) $this->getValue(self::FIELD_FEE_TYPE);
    }

    public function getFeeTaxClass(): string
    {
        return (string) $this->getValue(self::FIELD_FEE_TAX_CLASS);
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
        } catch (JsonException $e) {
            $result = [];
        }

        return $result;
    }
}
