<?php

namespace Billink\Billink\Model\Billink\Response;

use Billink\Billink\Gateway\Exception\InvalidResponseException;

/**
 * Class Response
 * @package Billink\Billink\Model\Billink\Response
 */
class Response implements ResponseInterface
{
    const INDEX_RESULT = 'RESULT';
    const INDEX_ERROR = 'ERROR';
    const INDEX_MSG = 'MSG';
    const INDEX_UUID = 'UUID';
    const INDEX_MSG_CODE = 'MSG/CODE';
    const INDEX_ERROR_CODE = 'ERROR/CODE';
    const INDEX_ERROR_DESCRIPTION = 'ERROR/DESCRIPTION';
    const INDEX_MSG_STATUSES_ITEM = 'MSG/STATUSES/ITEM';

    const RESULT_ERROR = 'ERROR';
    const RESULT_SUCCESS = 'MSG';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param array $data
     * @return $this
     * @throws InvalidResponseException
     */
    public function setData(array $data)
    {
        if (!is_array($data)) {
            throw new InvalidResponseException('Response data is invalid');
        }

        $this->data = $data;

        return $this;
    }


    /**
     * @return bool
     */
    public function hasData() {
        return !empty($this->data);
    }

    /**
     * @return bool
     * @throws InvalidResponseException
     */
    public function hasError()
    {
        if (!$this->data || !isset($this->data[self::INDEX_RESULT])) {
            throw new InvalidResponseException('Response data is invalid');
        }

        return $this->data[self::INDEX_RESULT] !== self::RESULT_SUCCESS;
    }

    /**
     * @return mixed
     * @throws InvalidResponseException
     */
    public function getErrorCode()
    {
        if (!$this->hasError()) {
            return false;
        }

        if (!isset($this->data[self::INDEX_ERROR])) {
            throw new InvalidResponseException('Error is not set');
        }

        return $this->getValueByPath(self::INDEX_ERROR_CODE);
    }

    public function getErrorDescription()
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
     * @param string|null $index
     * @return bool
     * @throws InvalidResponseException
     */
    public function getMsg($index = null)
    {
        if (!$this->data || !isset($this->data[self::INDEX_MSG])) {
            throw new InvalidResponseException('Invalid response data');
        }

        return $index !== null ? $this->getValueByPath($index) : $this->data[self::INDEX_MSG];
    }

    /**
     * @param string $path
     * @return mixed
     */
    protected function getValueByPath($path)
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
