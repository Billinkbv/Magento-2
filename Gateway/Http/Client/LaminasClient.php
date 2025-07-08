<?php

namespace Billink\Billink\Gateway\Http\Client;

use Laminas\Http\Client;
use Laminas\Http\Client\Adapter\Curl;

class LaminasClient extends Client
{
    /**
     * @param null|string $uri
     * @param null|array|\Traversable $options
     */
    public function __construct($uri = null, $options = null)
    {
        $this->setOptions([
            'useragent' => Client::class,
            'adapter' => Curl::class,
        ]);

        parent::__construct($uri, $options);
    }

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
