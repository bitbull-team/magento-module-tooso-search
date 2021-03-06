<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
class Bitbull_Tooso_Model_Observer
{
    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;
    protected $_client = null;

    public function __construct()
    {
        $this->_logger = Mage::helper('tooso/log');

        $this->_client = Mage::helper('tooso')->getClient();
    }

    /**
     * Regenerate search index
     *
     * @param  Mage_Cron_Model_Schedule $schedule
     * @return Bitbull_Tooso_Model_Observer
     */
    public function rebuildIndex(Mage_Cron_Model_Schedule $schedule)
    {
        if (Mage::helper('tooso')->isIndexEnabled()) {
            $this->_logger->log('Start scheduled reindex', Zend_Log::DEBUG);

            Mage::getModel('tooso/indexer')->rebuildIndex();

            $this->_logger->log('End scheduled reindex', Zend_Log::DEBUG);
        }

        return $this;
    }

    /**
     * Change title in some places, replacing original query
     * with fixed search string.
     *
     * @param Varien_Event_Observer $observer
    */
    public function showFixedSearchStringOnSearchResults(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $fixedSearchString = Mage::helper('tooso')->getFixedSearchString();
        $title = $block->__("Search results for: '%s'", $fixedSearchString ? $fixedSearchString : Mage::helper('catalogsearch')->getEscapedQueryText());

        if (Mage::helper('tooso')->isSearchEnabled() && $fixedSearchString) {

            if ($block instanceof Mage_CatalogSearch_Block_Result) {
                $block->setHeaderText($title);
            }

            // modify page title
            if ($block instanceof Mage_Page_Block_Html_Head) {
                $block->setTitle($title);
            }

            // add Home breadcrumb
            if ($block instanceof Mage_Page_Block_Html_Breadcrumbs) {
                $block->addCrumb('home', array(
                    'label' => $block->__('Home'),
                    'title' => $block->__('Go to Home Page'),
                    'link'  => Mage::getBaseUrl()
                ))->addCrumb('search', array(
                    'label' => $title,
                    'title' => $title
                ));
            }
        }
    }

    /**
     * Add tracking script that point to controller action endpoint
     * @param  Varien_Event_Observer $observer
     */
    public function includeTrackingScript(Varien_Event_Observer $observer){
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }
        $current_product = Mage::registry('current_product');
        if($current_product != null) {
            $layout = Mage::app()->getLayout();
            $block = Mage::helper('tooso/tracking')->getTrackingPixelBlock($current_product->getId());
            $layout->getBlock('before_body_end')->append($block);
        }else{
            $this->_logger->debug('Tracking script: product not found in request');
        }
    }

    /**
     * Save rank collection with SKU and their position from collection
     * @param  Varien_Event_Observer $observer
     */
    public function elaborateRankCollection(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }

        $this->_logger->debug('Rank Collection: elaborating collection..');
        $collection = Mage::registry('current_layer')->getProductCollection();

        $collection->addAttributeToSelect('name');
        $rankCollection = array();
        $i = 0;
        $curPage = (int) $collection->getCurPage();
        $pageSize = (int) $collection->getPageSize();
        $this->_logger->debug('Rank Collection: page '.$curPage.' size '.$pageSize);
        foreach ($collection as $product) {
            $id = $product->getId();
            $pos = $i + (($curPage-1) * $pageSize);
            $rankCollection[$id] = $pos;
            $this->_logger->debug('Rank Collection: ['.$id.'] '.$product->getName().' => '.$pos);
            $i++;
        }

        if(sizeof($rankCollection) == 0){
            $this->_logger->debug('Rank Collection: collection empty');
        }

        Mage::helper('tooso/session')->setRankCollection($rankCollection);
        $this->_logger->debug('Rank Collection: collection saved into session');

    }

    /**
     * Clear searchId session variable if no longer used
     * @param  Varien_Event_Observer $observer
     */
    public function clearSearchId(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('tooso')->isSearchEnabled()){
            return;
        }

        $routeName = Mage::app()->getRequest()->getRouteName();
        if($routeName != "catalog" && $routeName != "catalogsearch"){
            Mage::helper('tooso/session')->clearSearchId();
        }
    }

    /**
     * Track add to cart event
     * @param Varien_Event_Observer $observer
     */
    public function trackAddToCart(Varien_Event_Observer $observer)
    {
        if(!Mage::helper('tooso')->isTrackingEnabled()){
            return;
        }

        $productId = Mage::app()->getRequest()->getParam('product', null);
        if($productId != null){
            $product = Mage::getModel('catalog/product')->load($productId);
            $profilingParams = Mage::helper('tooso')->getProfilingParams();
            $sku = $product->getSku();

            $this->_client->productAddedToCart($sku, $profilingParams);
            $this->_logger->debug('Cart Traking: '.$sku.' added to cart');
        }else{
            $this->_logger->debug('Cart Traking: can\'t find product param');
        }
    }

}
