<?php

namespace Billink\Billink\Cron;

use Billink\Billink\Gateway\Command\MidpageGatewayCommand;
use Magento\Sales\Api\Data\OrderInterface;

class WhitelistStatus
{
    public function __construct(
        private readonly MidpageGatewayCommand $command
    ) {
    }

    public function execute(): void
    {
        /** @var OrderInterface $order */
        $this->command->execute([]);
    }
}
