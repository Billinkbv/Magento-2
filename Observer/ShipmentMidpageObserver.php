<?php

namespace Billink\Billink\Observer;

use Billink\Billink\Model\Ui\ConfigProvider;

class ShipmentMidpageObserver extends ShipmentObserver
{
    public const METHOD_CODE = ConfigProvider::CODE_MIDPAGE;
}
