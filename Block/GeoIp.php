<?php

namespace Hieu\GeoIp\Block;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;

class GeoIp extends \Magento\Framework\View\Element\Js\Components
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    public function __construct(
        Template\Context $context,EncoderInterface $encoder, array $data = [])
    {
        parent::__construct($context, $data);
        $this->encoder = $encoder;
    }

    /**
     * @return string
     */
    public function getGeoIpUrl() {
        return $this->_scopeConfig->getValue("web/secure/base_url") . 'rest/V1/hieu/geoip';
    }

    public function getCurrentUrl() {
        return $this->_storeManager->getStore()->getCurrentUrl(false);
    }

    public function getFullActionName() {
        return $this->_request->getFullActionName();
    }

    public function getParams() {
        return $this->encoder->encode($this->_request->getParams());
    }
}