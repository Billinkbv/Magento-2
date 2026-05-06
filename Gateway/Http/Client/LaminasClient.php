<?php

namespace Billink\Billink\Gateway\Http\Client;

use Laminas\Http\Client;
use Laminas\Http\Client\Adapter\Curl;
use Laminas\Http\Exception\RuntimeException;
use Laminas\Uri\Http;
use Traversable;

use function is_numeric;

class LaminasClient extends Client
{
    /**
     * @param null|string $uri
     * @param null|array|Traversable $options
     */
    public function __construct($uri = null, $options = null)
    {
        $this->setOptions(
            [
                'useragent' => Client::class,
                'adapter' => Curl::class,
            ]
        );

        parent::__construct($uri, $options);
    }
}
