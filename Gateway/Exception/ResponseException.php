<?php

namespace Billink\Billink\Gateway\Exception;

use Billink\Billink\Gateway\Helper\ErrorMessage;
use Exception;

class ResponseException extends Exception
{
    /**
     * @throws InvalidResponseException
     */
    public function __construct(
        int $code = 0,
        string $service = 'general',
        ?string $description = null,
        ?Exception $previous = null
    ) {
        $message = ErrorMessage::get($code, $service, $description);

        parent::__construct($message, $code, $previous);
    }
}
