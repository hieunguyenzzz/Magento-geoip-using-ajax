<?php

namespace Hieu\GeoIp\Model\Config\Source;

class CDN implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => 'Disable'],
            ['value' => 'GEOIP_COUNTRY_CODE', 'label' => 'Sonassi'],
            ['value' => 'HTTP_CF_IPCOUNTRY', 'label' => 'Cloudflare'],
        ];
    }
}