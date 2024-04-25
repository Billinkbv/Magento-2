<?php

namespace Billink\Billink\Gateway\Http\Client;

class LaminasClient extends \Magento\Framework\HTTP\LaminasClient
{
    protected function prepareHeaders($body, $uri)
    {
        $headers = parent::prepareHeaders($body, $uri);
        $result = [];
        // Fix magento issue https://github.com/magento/magento2/issues/37641
        foreach ($headers as $key => $value) {
            // Validate if the bug was fixed and the code return correct headers
            if (is_numeric($key)) {
                $result[] = $value;
                continue;
            }
            $result[] = $key . ': ' . $value;
        }
        return $result;
    }
}
