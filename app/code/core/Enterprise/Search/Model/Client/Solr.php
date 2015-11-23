<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Solr client
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_Client_Solr extends Apache_Solr_Service
{
    /**
     * Suggestions servlet
     *
     * @deprecated
     */
    const SUGGESTIONS_SERVLET = 'spell';

    /**
     * Store user login, that needed in authentication with solr server
     *
     * @var string
     */
    protected $_login = '';

    /**
     * Store user password, that needed in authentication with solr server
     *
     * @var string
     */
    protected $_password = '';

    /**
     * Constructed servlet full path URLs
     *
     * @deprecated
     *
     * @var string
     */
    protected $_suggestionsUrl;

    /**
     * Store curl adapter instance
     *
     * @var null|Varien_Http_Adapter_Curl
     */
    protected $_curlAdapter = null;



    /**
     * Initialize Solr Client
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $_optionsNames = array(
            'hostname',
            'login',
            'password',
            'port',
            'path'
        );
        if (!sizeof(array_intersect($_optionsNames, array_keys($options)))) {
            Mage::throwException(
                Mage::helper('enterprise_search')->__('Unable to perform search because of search engine missed configuration.')
            );
        }

        $this->_curlAdapter = new Varien_Http_Adapter_Curl();

        $this->setUserLogin($options['login']);
        $this->setPassword($options['password']);

        $this->setHost($options['hostname']);
        $this->setPort($options['port']);
        $this->setPath('/' . $options['path'] . '/');

        $this->_initUrls();

        $this->_defaultTimeout = (isset($options['timeout']) && (float)$options['timeout'] >= 0)
            ? (float)$options['timeout']
            : Enterprise_Search_Model_Adapter_Solr_Abstract::DEFAULT_TIMEOUT;

        return $this;
    }

    /**
     * Send an rollback command
     *
     * @param float|bool $timeout Maximum expected duration of the commit operation on the server
     *                            (otherwise, will throw a communication exception)
     *
     * @return Apache_Solr_Response
     */
    public function rollback($timeout = 3600)
    {
        $rawPost = '<rollback/>';
        return $this->_sendRawPost($this->_updateUrl, $rawPost, $timeout);
    }

    /**
     * Create a delete document based on a multiple queries and submit it
     *
     * @param array $rawQueries Expected to be utf-8 encoded
     * @param bool $fromPending
     * @param bool $fromCommitted
     * @param float|bool $timeout Maximum expected duration of the delete operation on the server
     *                            (otherwise, will throw a communication exception)
     *
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    public function deleteByQueries($rawQueries, $fromPending = true, $fromCommitted = true, $timeout = 3600)
    {
        $pendingValue = $fromPending ? 'true' : 'false';
        $committedValue = $fromCommitted ? 'true' : 'false';

        $rawPost = '<delete fromPending="' . $pendingValue . '" fromCommitted="' . $committedValue . '">';

        foreach ($rawQueries as $query)
        {
            //escape special xml characters
            $query = htmlspecialchars($query, ENT_NOQUOTES, 'UTF-8');

            $rawPost .= '<query>' . $query . '</query>';
        }

        $rawPost .= '</delete>';

        return $this->delete($rawPost, $timeout);
    }

    /**
     * Alias to Apache_Solr_Service::deleteByMultipleIds() method
     *
     * @param array $ids Expected to be utf-8 encoded strings
     * @param bool $fromPending
     * @param bool $fromCommitted
     * @param float|bool $timeout Maximum expected duration of the delete operation on the server
     *                            (otherwise, will throw a communication exception)
     *
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    public function deleteByIds($ids, $fromPending = true, $fromCommitted = true, $timeout = 3600)
    {
        $this->deleteByMultipleIds($ids, $fromPending, $fromCommitted, $timeout);
    }

    /**
     * Prepare basic options for curl adapter
     *
     * @param int|float|bool $timeout in seconds
     *
     * @return Enterprise_Search_Model_Client_Solr
     */
    protected function _setBasicAdapterOptions($timeout)
    {
        if ($timeout <= 0) {
            $timeout = $this->_defaultTimeout;
        }

        $optionsList = array(
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_TIMEOUT         => (int)$timeout
        );
        if (strlen($this->getUserLogin()) && strlen($this->getPassword())) {
            $optionsList[CURLOPT_HTTPAUTH]  = CURLAUTH_BASIC;
            $optionsList[CURLOPT_USERPWD]   = $this->getUserLogin() . ':' . $this->getPassword();
        }

        $this->_curlAdapter->setOptions($optionsList);

        return $this;
    }

    /**
     * Call the /admin/ping servlet, can be used to quickly tell if a connection to the server is able to be made
     *
     * @param float $timeout maximum time to wait for ping in seconds, -1 for unlimited (default is 2)
     *
     * @return float|bool
     */
    public function ping($timeout = 0)
    {
        $this->_setBasicAdapterOptions($timeout);
        $this->_curlAdapter->addOptions(array(
                CURLOPT_HEADER  => 0,
                CURLOPT_NOBODY  => 1))
            ->write(Zend_Http_Client::GET, $this->_pingUrl);

        $totalTime = microtime(1);
        $this->_curlAdapter->read();
        $result = ($this->_curlAdapter->getInfo(CURLINFO_HTTP_CODE) == 200)
            ? round(microtime(1) - $totalTime, 3)
            : false;
        $this->_curlAdapter->close();

        return $result;
    }

    /**
     * Read response for prepared curl request
     *
     * @return Apache_Solr_Response
     *
     * @throws Exception If a non 200 response status is returned
     */
    protected function _getResponse()
    {
        $response = $this->_curlAdapter->read();
        $this->_curlAdapter->close();

        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        $headers = explode("\r\n", $headers);
        $response = new Apache_Solr_Response($body, $headers, $this->_createDocuments,
            $this->_collapseSingleValueArrays);

        $httpStatus = $response->getHttpStatus();
        if ($httpStatus != 200) {
            throw new Exception('"' . $httpStatus . '" Status: ' . $response->getHttpStatusMessage(), $httpStatus);
        }

        return $response;
    }

    /**
     * Central method for making a get operation against this Solr Server
     *
     * @param string $url
     * @param int|bool $timeout Read timeout in seconds
     *
     * @return Apache_Solr_Response
     */
    protected function _sendRawGet($url, $timeout = false)
    {
        $this->_setBasicAdapterOptions($timeout);
        $this->_curlAdapter->write(Zend_Http_Client::GET, $url);

        return $this->_getResponse();
    }

    /**
     * Central method for making a post operation against this Solr Server
     *
     * @param string $url
     * @param string $rawPost
     * @param int|bool $timeout Read timeout in seconds
     * @param string $contentType
     *
     * @return Apache_Solr_Response
     */
    protected function _sendRawPost($url, $rawPost, $timeout = false, $contentType = 'text/xml; charset=UTF-8')
    {
        $headers = array('Content-Type: ' . $contentType);

        $this->_setBasicAdapterOptions($timeout);
        $this->_curlAdapter->addOptions(array(CURLOPT_HEADER => 1))
            ->write(Zend_Http_Client::POST, $url, '1.1', $headers, $rawPost);

        return $this->_getResponse();
    }

    /**
     * Setter for solr server username
     *
     * @param string $username
     */
    public function setUserLogin($username)
    {
        $this->_login = (string)$username;
    }

    /**
     * Getter of solr server username
     *
     * @return string
     */
    public function getUserLogin()
    {
        return $this->_login;
    }

    /**
     * Setter for solr server password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->_password = (string)$password;
    }

    /**
     * Getter of solr server password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Simple Search interface
     *
     * @deprecated after 1.9.0.0 - integrated into $this->combinedSearch()
     *
     * @param string $query The raw query string
     * @param array $params key / value pairs for other query parameters (see Solr documentation),
     *                      use arrays for parameter keys used more than once (e.g. facet.field)
     * @param string $method
     *
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    public function searchSuggestions($query, $params = array(), $method = self::METHOD_GET)
    {
        if (!is_array($params)) {
            $params = array();
        }
        // construct our full parameters, sending the version is important in case the format changes
        $params['version'] = self::SOLR_VERSION;

        // common parameters in this interface
        $params['wt'] = self::SOLR_WRITER;
        $params['json.nl'] = $this->_namedListTreatment;

        $params['q'] = $query;

        /**
         * use http_build_query to encode our arguments
         * because its faster than url encoding all the parts ourselves in a loop
         */
        $queryString = http_build_query($params, null, $this->_queryStringDelimiter);

        $queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);

        if ($method == self::METHOD_GET) {
            return $this->_sendRawGet($this->_suggestionsUrl . $this->_queryDelimiter . $queryString);
        } elseif ($method == self::METHOD_POST) {
            return $this->_sendRawPost(
                $this->_suggestionsUrl, $queryString, false, 'application/x-www-form-urlencoded'
            );
        } else {
            throw new Exception("Unsupported method '$method', please use the Apache_Solr_Service::METHOD_* constants");
        }
    }

    /**
     * Simple Search interface
     *
     * @param string $query The raw query string
     * @param int $offset The starting offset for result documents
     * @param int $limit The maximum number of result documents to return
     * @param array $params key / value pairs for other query parameters (see Solr documentation), use arrays for parameter keys used more than once (e.g. facet.field)
     * @param string $method
     *
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    public function search($query, $offset = 0, $limit = 10, $params = array(), $method = self::METHOD_GET)
    {
        if (!is_array($params)) {
            $params = array();
        }

        // construct our full parameters sending the version is important in case the format changes
        $params['version']  = self::SOLR_VERSION;

        // common parameters in this interface
        $params['wt']       = self::SOLR_WRITER;
        $params['json.nl']  = $this->_namedListTreatment;

        $params['q']        = $query;
        $params['start']    = $offset;
        $params['rows']     = $limit;

        // use http_build_query to encode our arguments because its faster
        // than url encoding all the parts ourselves in a loop
        $queryString = http_build_query($params, null, $this->_queryStringDelimiter);

        // because http_build_query treats arrays differently than we want to, correct the query
        // string by changing foo[#]=bar (# being an actual number) parameter strings to just
        // multiple foo=bar strings. This regex should always work since '=' will be url encoded
        // anywhere else the regex isn't expecting it
        $queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);

        if ($method == self::METHOD_GET) {
            return $this->_sendRawGet($this->_searchUrl . $this->_queryDelimiter . $queryString);
        } elseif ($method == self::METHOD_POST) {
            return $this->_sendRawPost($this->_searchUrl, $queryString, false,
                'application/x-www-form-urlencoded; charset=UTF-8');
        } else {
            throw new Exception("Unsupported method '$method', please use the Apache_Solr_Service::METHOD_* constants");
        }
    }
}
