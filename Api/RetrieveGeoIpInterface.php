<?php

namespace Hieu\GeoIp\Api;

interface RetrieveGeoIpInterface {

    /**
     * @param string $currentUrl
     * @param string $fullActionName
     * @param string[] $params
     * @return mixed
     */
    public function getLocation($currentUrl, $fullActionName, $params);
}