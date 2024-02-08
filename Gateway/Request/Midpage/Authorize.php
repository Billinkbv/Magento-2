<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;

class Authorize implements BuilderInterface
{
    public const USER_NAME = 'billinkUsername';
    public const USER_ID = 'billinkID';
    public const WORKFLOW = 'workflowNumber';

    private MidpageConfig $midpageConfig;

    public function __construct(
        MidpageConfig $midpageConfig
    ) {
        $this->midpageConfig = $midpageConfig;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $data = [];
        $data[self::USER_NAME] = $this->midpageConfig->getAccountName();
        $data[self::USER_ID] = $this->midpageConfig->getAccountId();
        $data[self::WORKFLOW] = '1';

        return ['client' => $data];
    }
}
