<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;

class TransactionManager
{
    public const HASH_ID = 'hash';
    public const TRANSACTION_ID = 'id';

    public function __construct(
        protected readonly MidpageConfig $midpageConfig,
        protected readonly EncryptorInterface $encryptor,
        protected readonly SerializerInterface $serializer
    ) {
    }

    public function createTransactionId(string $incrementId): string
    {
        $hash = $this->createHash($incrementId);
        $data = [
            self::HASH_ID => $hash,
            self::TRANSACTION_ID => $incrementId
        ];
        $txn = $this->encryptor->encrypt($this->serializer->serialize($data));
        return urlencode($txn);
    }

    public function createHash(string $incrementId): string
    {
        // Add account id as salt to provide a bigger string
        $hashValue = $incrementId . $this->midpageConfig->getAccountId();
        return $this->encryptor->hash($hashValue);
    }

    public function validateTransaction(string $transactionId): ?string
    {
        try {
            // Decrypt data
            // Urls by default are encoded, so this value should be correct
            $data = $this->encryptor->decrypt($transactionId);
            // In case it's not - try to decode and validate again
            if ($data === '') {
                $data = urldecode($transactionId);
                $data = $this->encryptor->decrypt($data);
            }
            $data = $this->serializer->unserialize($data);
            if (!isset($data[self::HASH_ID], $data[self::TRANSACTION_ID])) {
                return null;
            }
            $orderId = $data[self::TRANSACTION_ID];
            $transactionHash = $data[self::HASH_ID];
            // Compare hashes
            $newHash = $this->createHash($orderId);
            if ($newHash === $transactionHash) {
                return $orderId;
            }
        } catch (\Exception $e) {
        }
        return null;
    }
}