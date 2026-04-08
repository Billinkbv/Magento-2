<?php

namespace Billink\Billink\Gateway\Validator\StartWorkflow;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Billink\Billink\Gateway\Helper\Gateway;
use Billink\Billink\Gateway\Validator\AbstractResponseValidator;
use Billink\Billink\Model\Billink\Response\Response;

use function array_merge;

class ResponseValidator extends AbstractResponseValidator
{
    public const RESULT_SUCCESS = 500;

    protected string $service = Gateway::SERVICE_START_WORKFLOW;

    public function getResponseValidators(): array
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                static fn($response) => match ($response->getMsg(Response::INDEX_MSG_CODE)) {
                    self::RESULT_SUCCESS => ['result' => true],
                    default => throw new InvalidResponseException('Invalid StartWorkflow result'),
                }
            ]
        );
    }
}
