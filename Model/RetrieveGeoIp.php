<?php

namespace Hieu\GeoIp\Model;

use Hieu\GeoIp\Api\RetrieveGeoIpInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class RetrieveGeoIp implements RetrieveGeoIpInterface
{
    const GEOIP_BY_CDN_PATH = 'general/geo_ip/cdn';
    const DEFAULT_COUNTRY = 'GB';

    /**
     * @var RequestInterface
     */
    private $httpRequest;
    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        EncoderInterface $jsonEncoder,
        RequestInterface $httpRequest,
        StoreRepositoryInterface $storeRepository,
        UrlFinderInterface $urlFinder,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->httpRequest = $httpRequest;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeRepository = $storeRepository;
        $this->urlFinder = $urlFinder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation($currentUrl, $fullActionName, $params)
    {
        $serverVariable = $this->scopeConfig->getValue(self::GEOIP_BY_CDN_PATH);

        if ($serverVariable) {
            $geoipCountry = $this->httpRequest->getServer($serverVariable);
        } else {
            $geoipCountry = self::DEFAULT_COUNTRY;
        }

        $currentUrlArr = explode('/', str_replace(['/index.php', 'http://', 'https://'], '', $currentUrl));
        $store = $this->_getStoreByCountry($geoipCountry);

        // if user is expecting another store than the current he is on
        if ($currentUrlArr[1] != $store->getCode()) {
            $expectedtUrl = $this->_generateCorrectUrlByCountryCode($store, $fullActionName, $params);
            file_put_contents(BP . '/var/log/mobelaris/geoip.log', "$expectedtUrl\n", FILE_APPEND);
            return $this->jsonEncoder->encode(['url' => $expectedtUrl]);
        }
        return false;
    }

    /**
     * @return StoreInterface
     */
    protected function _getStoreByCountry($country) {
        $defaultStore = '';
        foreach ($this->storeRepository->getList() as $store) {
            if ($store->getCode() == 'en') {
                $defaultStore = $store;
            }
            $countries = unserialize($store->getData('geoip_country_code'));
            if (in_array($country, $countries)) {
                return $store;
            }
        }
        return $defaultStore;
    }

    /**
     * @param $store StoreInterface
     * @param $fullActionName
     * @param $params
     * @return bool|string
     */
    protected function _generateCorrectUrlByCountryCode($store, $fullActionName, $params) {
        $result = '';
        $id = !empty($params['id']) ? $params['id'] : false;
        if ($fullActionName == 'catalog_category_view' && $id) {
            $url = $this->_getRewriteUrl($params['id'],CategoryUrlRewriteGenerator::ENTITY_TYPE, $store);
            if (!$url) {
                $url = $store->getBaseUrl()  . 'catalog/category/view/id/' . $id;
            }
        } elseif ($fullActionName == 'catalog_product_view' && $id) {
            $url = $this->_getRewriteUrl($id, 'product', $store);
            if (!$result) {
                $url = $store->getBaseUrl()  . 'catalog/product/view/id/' . $id;
            }
        } else {
            $url = $store->getBaseUrl();
        }

        return $url;
    }

    /**
     * @param $entityId
     * @param $entityType
     * @param $store StoreInterface
     * @return bool|string
     */
    protected function _getRewriteUrl($entityId, $entityType, $store) {
       $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::ENTITY_ID => $entityId,
            UrlRewrite::ENTITY_TYPE => $entityType,
            UrlRewrite::STORE_ID => $store->getId(),
        ]);

        if ($rewrite) {
            return $store->getBaseUrl() . $rewrite->getRequestPath();
        }

        return false;
    }
}