<?php

namespace Billink\Billink\Gateway\Validator\Order\Refund;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Billink\Billink\Gateway\Helper\Gateway;
use Billink\Billink\Gateway\Validator\AbstractResponseValidator;
use Billink\Billink\Model\Billink\Response\Response;
use Closure;

use function array_merge;
use function is_numeric;
use function key;

class ResponseValidator extends AbstractResponseValidator
{
    public const RESULT_SUCCESS = 200;

    protected string $service = Gateway::SERVICE_ORDER;

    /**
     * @return array|Closure[]
     */
    public function getResponseValidators(): array
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                function ($response) {
                    $rows = $response->getMsg(Response::INDEX_MSG_STATUSES_ITEM);
                    if (is_numeric(key($rows))) {
                        foreach ($rows as $row) {
                            $this->validateItem($row['CODE']);
                        }
                    } else {
                        $this->validateItem($rows['CODE']);
                    }

                    return ['result' => true];
                }
            ]
        );
    }

    /**
     * @throws InvalidResponseException
     */
    private function validateItem(int|string $code): void
    {
        if ((int) $code !== self::RESULT_SUCCESS) {
            throw new InvalidResponseException('Invalid Credit result');
        }
    }
}
