<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
*/
class Bitbull_Tooso_Model_Suggest
{
    /**
     * Client for API comunication
     *
     * @var Bitbull_Tooso_Client
     */
    protected $_client;

    /**
     * Represents a Tooso search result.
     *
     * @var Bitbull_Tooso_Suggest_Result
     */
    protected $_result;

    /**
     * @var Bitbull_Tooso_Helper_Log
     */
    protected $_logger = null;

    /**
     * Constructor, retrieve config for connection to Tooso API.
     */
    public function __construct()
    {
        $apiKey = Mage::getStoreConfig('tooso/server/api_key');
        $language = 'en'; // @todo make configurable

        $this->_client = new Bitbull_Tooso_Client($apiKey, $language);

        $this->_logger = Mage::helper('tooso/log');
    }

    /**
     * @param string $query
     * @return Bitbull_Tooso_Model_Suggest
     */
    public function suggest($query)
    {
        $query = preg_quote($query);
        if ($query) {
            try {
                $result = $this->_client->suggest($query);
                $this->setResult($result);
            } catch (Exception $e) {
                $this->_logger->logException($e);
            }
        }

        return $this;
    }

    /**
     * Set Tooso response
     *
     * @param Bitbull_Tooso_Suggest_Result $result
     */
    public function setResult(Bitbull_Tooso_Suggest_Result $result)
    {
        $this->_result = $result;
    }

    public function getSuggestions()
    {
        $suggestions = array();

        if (!is_null($this->_result)) {
            $results = $this->_result->getSuggestions();

            foreach ($results as $suggestedString => $occurrence) {
                $suggestions[] = new Varien_Object(array(
                    'query_text' => $suggestedString,
                    'num_results' => (int)$occurrence
                ));
            }
        }

        return $suggestions;
    }
}