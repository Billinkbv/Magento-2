<?php

namespace Billink\Billink\Gateway\Validator\Check;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Billink\Billink\Gateway\Helper\Gateway;
use Billink\Billink\Gateway\Validator\AbstractResponseValidator;
use Billink\Billink\Model\Billink\Response\Response;

use function array_merge;

class ResponseValidator extends AbstractResponseValidator
{
    public const RESULT_TRUSTED = 500;
    public const RESULT_UNTRUSTED = 501;

    protected string $service = Gateway::SERVICE_CHECK;

    public function getResponseValidators(): array
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                static fn($response) => match ($response->getMsg(Response::INDEX_MSG_CODE)) {
                    self::RESULT_TRUSTED => ['result' => true],
                    self::RESULT_UNTRUSTED => ['result' => false, 'code' => self::RESULT_UNTRUSTED],
                    default => throw new InvalidResponseException('Unknown response code'),
                }
            ]
        );
    }
}
