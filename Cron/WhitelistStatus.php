<?php

namespace Billink\Billink\Cron;

use Billink\Billink\Gateway\Command\MidpageGatewayCommand;

class WhitelistStatus
{
    public function __construct(
        private readonly MidpageGatewayCommand $command
    ) {
    }

    public function execute(): void
    {
        $this->command->execute([]);
    }
}
