<?php

namespace Billink\Billink\Model\Billink\Response;

interface ResponseInterface
{
    /**
     * @return bool
     */
    public function hasError();

    /**
     * @return mixed
     */
    public function getErrorCode();

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data);

    /**
     * @return mixed
     */
    public function getMsg();
}