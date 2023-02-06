<?php

namespace Billink\Billink\Gateway\Helper;

use Billink\Billink\Gateway\Exception\InvalidResponseException;

/**
 * Class ErrorMessage
 * @package Billink\Billink\Gateway\Helper
 */
class ErrorMessage
{
    /**
     * @param string $code
     * @param string $service
     * @param string|null $errorDescription
     * @return \Magento\Framework\Phrase
     * @throws InvalidResponseException
     */
    public static function get($code, $service, $errorDescription = null)
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
