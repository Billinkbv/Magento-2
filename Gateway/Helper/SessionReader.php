<?php

namespace Billink\Billink\Gateway\Helper;

class SessionReader
{
    public const REDIRECT_URL = 'sessionURL';

    public function getResponse(array $responseObject): array
    {
        $response = ['status' => 'error', 'message' => 'Incorrect response'];
        try {
            if (\is_string($responseObject[0])) {
                $response = \json_decode($responseObject[0], true);
            }
        } catch (\Exception $e) {
            // Use default error above
        }
        return $response;
    }
}
