<?php

namespace Billink\Billink\Gateway\Validator\Order\Refund;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Billink\Billink\Gateway\Helper\Gateway;
use Billink\Billink\Gateway\Validator\AbstractResponseValidator;
use Billink\Billink\Model\Billink\Response\Response;

/**
 * Class ResponseValidator
 *
 * @package Billink\Billink\Gateway\Validator\Order
 */
class ResponseValidator extends AbstractResponseValidator
{
    const RESULT_SUCCESS = 200;
    /**
     * @var string
     */
    protected $service = Gateway::SERVICE_ORDER;

    /**
     * @return array|\Closure[]
     */
    public function getResponseValidators()
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                function($response) {
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
     * @param $code
     *
     * @throws InvalidResponseException
     */
    private function validateItem($code)
    {
        if ((int)$code !== self::RESULT_SUCCESS) {
            throw new InvalidResponseException('Invalid Credit result');
        }
    }
}
