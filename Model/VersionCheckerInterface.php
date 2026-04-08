<?php

namespace Billink\Billink\Model;

interface VersionCheckerInterface
{
    /**
     * @api
     */
    public function getRemoteVersion(): string;
}
