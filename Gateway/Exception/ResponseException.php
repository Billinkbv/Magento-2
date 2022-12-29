<?php

namespace Billink\Billink\Gateway\Exception;

use Billink\Billink\Gateway\Helper\ErrorMessage;

/**
 * Class ResponseException
 * @package Billink\Billink\Gateway\Exception
 */
class ResponseException extends \Exception
{
    /**
     * ResponseException constructor.
     * @param int $code
     * @param string $service
     * @param \Exception|null $previous
     */
    public function __construct($code = 0, $service = 'general', $description = null, \Exception $previous = null)
    {
        $message = ErrorMessage::get($code, $service, $description);

        parent::__construct($message, $code, $previous);
    }
}
