<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLE_SEARCH = 'tooso/active/frontend';

    const XML_PATH_ENABLE_INDEX = 'tooso/active/index';

    const XML_PATH_SUGGEST_MAX_RESULTS = 'tooso/suggest/max_results';

    protected $_fixedSearchString = null;
    
    protected $_searchId = null;

    protected $_products = null;

    public function isSearchEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_SEARCH, $store);
    }

    public function isIndexEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_INDEX, $store);
    }

    public function getSuggestMaxResults($store = null)
    {
        return Mage::getConfig(self::XML_PATH_SUGGEST_MAX_RESULTS, $store);
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * @param array $products
     */
    public function setProducts($products)
    {
        $this->_products = $products;
    }

    /**
     * @param string $fixedSearchString
     */
    public function setFixedSearchString($fixedSearchString)
    {
        $this->_fixedSearchString = $fixedSearchString;
    }

    /**
     * @return string
     */
    public function getFixedSearchString()
    {
        return $this->_fixedSearchString;
    }

    /**
     * @param string $searchId
     */
    public function setSearchId($searchId)
    {
        $this->_searchId = $searchId;
    }

    /**
     * @return string
     */
    public function getSearchId()
    {
        return $this->_searchId;
    }

    public function isTypoCorrectedSearch()
    {
        return Mage::app()->getRequest()->getParam('typoCorrection', 'true') == 'true';
    }
    
    /**
     * Create and configure a Tooso API Client instance
     * 
     * @return Bitbull_Tooso_Client
    */
    public function getClient()
    {
        $apiKey = Mage::getStoreConfig('tooso/server/api_key');
        $language = Mage::app()->getLocale()->getLocaleCode();
        $storeCode = Mage::app()->getStore()->getCode();

        $logger = Mage::helper('tooso/log');

        $client = new Bitbull_Tooso_Client($apiKey, $language, $storeCode, $logger);

        $client->setReportSender(Mage::helper('tooso/log_send'));

        return $client;
    }
    
    /**
     * @return array
    */
    public function getProfilingParams()
    {
        $customerSession = Mage::getSingleton('customer/session');
        $sessionId = Mage::getSingleton('core/session')->getSessionId();

        if ($customerSession->isLoggedIn()) {
            $userId = $customerSession->getCustomerId();
        } else {
            $userId = $sessionId;
        }

        return array(
            'userId' => $userId,
            'sessionId' => $sessionId,
        );
    }
}