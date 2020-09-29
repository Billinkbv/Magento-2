<?php

namespace Billink\Billink\Gateway\Validator\Order;

use Billink\Billink\Gateway\Exception\InvalidResponseException;
use Billink\Billink\Gateway\Helper\Gateway;
use Billink\Billink\Gateway\Validator\AbstractResponseValidator;
use Billink\Billink\Model\Billink\Response\Response;

/**
 * Class ResponseValidator
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
     * @return array
     */
    public function getResponseValidators()
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                function ($response) {
                    switch ($response->getMsg(Response::INDEX_MSG_CODE)) {
                        case self::RESULT_SUCCESS:
                            return ['result' => true];
                        default:
                            throw new InvalidResponseException('Invalid Order result');
                    }
                }
            ]
        );
    }
}