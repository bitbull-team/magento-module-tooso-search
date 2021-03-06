<?php
/**
 * @package Bitbull_Tooso
 * @author Gennaro Vietri <gennaro.vietri@bitbull.it>
 */

class Bitbull_Tooso_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLE_SEARCH = 'tooso/active/frontend';

    const XML_PATH_ENABLE_INDEX = 'tooso/active/index';

    const XML_PATH_ENABLE_TRACKING = 'tooso/active/tracking';

    const XML_PATH_SUGGEST_MAX_RESULTS = 'tooso/suggest/max_results';

    const XML_PATH_SERVER_APIKEY = 'tooso/server/api_key';

    const XML_PATH_SERVER_API_BASEURL = 'tooso/server/api_base_url';

    protected $_fixedSearchString = null;
    
    protected $_searchId = null;

    protected $_products = null;

    protected $_searchAlreadyDone = false;

    public function isSearchEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_SEARCH, $store);
    }

    public function isIndexEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_INDEX);
    }

    public function isTrackingEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_TRACKING, $store);
    }

    public function getSuggestMaxResults($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_SUGGEST_MAX_RESULTS, $store);
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
     * @return string
     */
    public function getParentSearchId()
    {
        return Mage::app()->getRequest()->getParam('parentSearchId');
    }
    
    /**
     * Create and configure a Tooso API Client instance
     * 
     * @return Bitbull_Tooso_Client
    */
    public function getClient($storeCode = null, $language = null)
    {
        $apiKey = Mage::getStoreConfig(self::XML_PATH_SERVER_APIKEY);
        $apiBaseUrl = Mage::getStoreConfig(self::XML_PATH_SERVER_API_BASEURL);
        if($language == null){
            $language = Mage::app()->getLocale()->getLocaleCode();
        }
        if($storeCode == null){
            $storeCode = Mage::app()->getStore()->getCode();
        }
        $client = new Bitbull_Tooso_Client($apiKey, $apiBaseUrl, $language, $storeCode);

        $client->setLogger(Mage::helper('tooso/log'));
        $client->setReportSender(Mage::helper('tooso/log_send'));
        $client->setSessionStorage(Mage::helper('tooso/session'));

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

    /**
     * Set SearchAlreadyDone to true
     */

    public function setSearchAlreadyDone(){
        $this->_searchAlreadyDone = true;
    }

    /**
     * @return boolean
     */

    public function getSearchAlreadyDone(){
        return $this->_searchAlreadyDone;
    }

    /**
     * Get product attributes
     */

    public function getAttributesToIndex(){

    }
}