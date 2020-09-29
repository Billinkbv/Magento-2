<?php


namespace Billink\Billink\Model;


interface VersionCheckerInterface
{
    /**
     * @api
     * @return mixed
     */
    public function getRemoteVersion();
}