<?php

namespace Billink\Billink\Gateway\Request\Midpage;

use Billink\Billink\Gateway\Config\MidpageConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizePlain implements BuilderInterface
{
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
        $data[Authorize::USER_NAME] = $this->midpageConfig->getAccountName();
        $data[Authorize::USER_ID] = $this->midpageConfig->getAccountId();

        return $data;
    }
}
