<?php

namespace Billink\Billink\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class BillinkError extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/billink.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::ERROR;
}
