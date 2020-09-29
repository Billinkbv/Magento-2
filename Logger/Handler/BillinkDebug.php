<?php

namespace Billink\Billink\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class BillinkDebug
 * @package Billink\Billink\Logger\Handler
 */
class BillinkDebug extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/billink.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}