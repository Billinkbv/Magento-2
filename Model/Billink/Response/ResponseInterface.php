<?php

namespace Billink\Billink\Model\Billink\Response;

interface ResponseInterface
{
    public function hasError(): bool;

    public function getErrorCode(): mixed;

    public function setData(array $data): static;

    public function getMsg(): mixed;
}
