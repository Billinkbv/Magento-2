<?php

namespace Billink\Billink\Model\Billink\Response;

use Billink\Billink\Gateway\Exception\InvalidResponseException;

use function explode;

class Response implements ResponseInterface
{
    public const INDEX_RESULT = 'RESULT';
    public const INDEX_ERROR = 'ERROR';
    public const INDEX_MSG = 'MSG';
    public const INDEX_UUID = 'UUID';
    public const INDEX_MSG_CODE = 'MSG/CODE';
    public const INDEX_ERROR_CODE = 'ERROR/CODE';
    public const INDEX_ERROR_DESCRIPTION = 'ERROR/DESCRIPTION';
    public const INDEX_MSG_STATUSES_ITEM = 'MSG/STATUSES/ITEM';

    public const RESULT_ERROR = 'ERROR';
    public const RESULT_SUCCESS = 'MSG';

    private array $data = [];

    /**
     * @throws InvalidResponseException
     */
    public function setData(array $data): static
    {
        if (!is_array($data)) {
            throw new InvalidResponseException('Response data is invalid');
        }

        $this->data = $data;

        return $this;
    }

    public function hasData(): bool
    {
        return count($this->data) !== 0;
    }

    /**
     * @throws InvalidResponseException
     */
    public function hasError(): bool
    {
        if (!$this->data || !isset($this->data[self::INDEX_RESULT])) {
            throw new InvalidResponseException('Response data is invalid');
        }

        return $this->data[self::INDEX_RESULT] !== self::RESULT_SUCCESS;
    }

    /**
     * @throws InvalidResponseException
     */
    public function getErrorCode(): mixed
    {
        if (!$this->hasError()) {
            return false;
        }

        if (!isset($this->data[self::INDEX_ERROR])) {
            throw new InvalidResponseException('Error is not set');
        }

        return $this->getValueByPath(self::INDEX_ERROR_CODE);
    }

    /**
     * @throws InvalidResponseException
     */
    public function getErrorDescription(): mixed
    {
        if (!$this->hasError()) {
            return false;
        }

        if (!isset($this->data[self::INDEX_ERROR])) {
            throw new InvalidResponseException('Error is not set');
        }

        return $this->getValueByPath(self::INDEX_ERROR_DESCRIPTION);
    }

    /**
     * @throws InvalidResponseException
     */
    public function getMsg(?string $index = null): bool
    {
        if (!$this->data || !isset($this->data[self::INDEX_MSG])) {
            throw new InvalidResponseException('Invalid response data');
        }

        return $index !== null ? $this->getValueByPath($index) : $this->data[self::INDEX_MSG];
    }

    protected function getValueByPath(string $path): mixed
    {
        $indexes = explode('/', $path);

        if (!isset($this->data[$indexes[0]])) {
            return false;
        }

        $result = false;

        foreach ($indexes as $index) {
            if (!$result) {
                $result = $this->data[$indexes[0]];
                continue;
            }

            if (!isset($result[$index])) {
                return false;
            }

            $result = $result[$index];
        }

        return $result;
    }
}
