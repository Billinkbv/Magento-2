<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Exception\InvalidResponseException;

use function __;
use function strtoupper;

class ErrorMessage
{
    /**
     * @throws InvalidResponseException
     */
    public static function get(string $code, string $service, ?string $errorDescription = null): string
    {
        $messageId = 'billink_' . $service . '_error_code_' . $code;
        $message = __($messageId);

        if ($messageId == $message) {
            throw new InvalidResponseException(
                'Got error ' . $code . ' from ' . strtoupper($service) .
                ' service: ' . $errorDescription
            );
        }

        return $message . ': ' . $errorDescription;
    }
}
