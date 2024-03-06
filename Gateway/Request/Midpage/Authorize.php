<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;

class Authorize implements BuilderInterface
{
    public const USER_NAME = 'billinkUsername';
    public const USER_ID = 'billinkID';
    public const WORKFLOW = 'workflowNumber';

    public function __construct(
        protected readonly MidpageConfig $midpageConfig
    ) {
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $data = [];
        $data[self::USER_NAME] = $this->midpageConfig->getAccountName();
        $data[self::USER_ID] = $this->midpageConfig->getAccountId();
        $data[self::WORKFLOW] = '1';

        return ['client' => $data];
    }
}
