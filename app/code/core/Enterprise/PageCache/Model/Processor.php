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
 * @package     Enterprise_PageCache
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_PageCache_Model_Processor
{
    const NO_CACHE_COOKIE               = 'NO_CACHE';
    const XML_NODE_ALLOWED_CACHE        = 'frontend/cache/requests';
    const XML_PATH_ALLOWED_DEPTH        = 'system/page_cache/allowed_depth';
    /**
     * @deprecated after 1.8.0.0
     */
    const XML_PATH_LIFE_TIME            = 'system/page_cache/lifetime';
    const XML_PATH_CACHE_MULTICURRENCY  = 'system/page_cache/multicurrency';
    const XML_PATH_CACHE_DEBUG          = 'system/page_cache/debug';
    const REQUEST_ID_PREFIX             = 'REQEST_';
    const CACHE_TAG                     = 'FPC';  // Full Page Cache, minimize
    const DESIGN_EXCEPTION_KEY          = 'FPC_DESIGN_EXCEPTION_CACHE';
    const DESIGN_CHANGE_CACHE_SUFFIX    = 'FPC_DESIGN_CHANGE_CACHE';
    const CACHE_SIZE_KEY                = 'FPC_CACHE_SIZE_CAHCE_KEY';
    const SSL_OFFLOADER_HEADER_KEY      = 'FPC_SSL_OFFLOADER_HEADER_CACHE';
    const XML_PATH_CACHE_MAX_SIZE       = 'system/page_cache/max_cache_size';
    const REQUEST_PATH_PREFIX           = 'REQUEST_PATH_';

    /**
     * @deprecated after 1.8.0.0 - moved to Enterprise_PageCache_Model_Container_Viewedproducts
     */
    const LAST_PRODUCT_COOKIE           = 'LAST_PRODUCT';

    const METADATA_CACHE_SUFFIX        = '_metadata';

    /**
     * Action name for 404 page
     */
    const NOT_FOUND_ACTION = 'noroute';

    /**
     * Request identifier
     *
     * @var string
     */
    protected $_requestId;

    /**
     * Request page cache identifier
     *
     * @var string
     */
    protected $_requestCacheId;

    /**
     * Cache tags related with request
     * @var array
     */
    protected $_requestTags;

    /**
     * Cache service info
     * @var mixed
     */
    protected $_metaData = null;

    /**
     * Flag whether design exception value presents in cache
     * It always must be present (maybe serialized empty value)
     * @var boolean
     */
    protected $_designExceptionExistsInCache = false;

    /**
     * Request processor model
     * @var mixed
     */
    protected $_requestProcessor = null;

    /**
     * subprocessor model
     * @var mixed
     */
    protected $_subprocessor;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_createRequestIds();
        $this->_requestTags     = array(self::CACHE_TAG);
    }

    /**
     * Populate request ids
     * @return Enterprise_PageCache_Model_Processor
     */
    protected function _createRequestIds()
    {
        $uri = $this->_getFullPageUrl();

        //Removing get params
        $pieces = explode('?', $uri);
        $uri = array_shift($pieces);

        /**
         * Define COOKIE state
         */
        if ($uri) {
            if (isset($_COOKIE[Mage_Core_Model_Store::COOKIE_NAME])) {
                $uri = $uri.'_'.$_COOKIE[Mage_Core_Model_Store::COOKIE_NAME];
            }
            if (isset($_COOKIE['currency'])) {
                $uri = $uri.'_'.$_COOKIE['currency'];
            }
            if (isset($_COOKIE[Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_GROUP])) {
                $uri .= '_' . $_COOKIE[Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_GROUP];
            }
            if (isset($_COOKIE[Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_LOGGED_IN])) {
                $uri .= '_' . $_COOKIE[Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_LOGGED_IN];
            }
            if (isset($_COOKIE[Enterprise_PageCache_Model_Cookie::CUSTOMER_SEGMENT_IDS])) {
                $uri .= '_' . $_COOKIE[Enterprise_PageCache_Model_Cookie::CUSTOMER_SEGMENT_IDS];
            }
            if (isset($_COOKIE[Enterprise_PageCache_Model_Cookie::IS_USER_ALLOWED_SAVE_COOKIE])) {
                $uri .= '_' . $_COOKIE[Enterprise_PageCache_Model_Cookie::IS_USER_ALLOWED_SAVE_COOKIE];
            }
            if (Enterprise_PageCache_Helper_Data::isSSL()) {
                $uri .= '_ssl';
            }
            $designPackage = $this->_getDesignPackage();

            if ($designPackage) {
                $uri .= '_' . $designPackage;
            }
        }

        $this->_requestId       = $uri;
        $this->_requestCacheId  = $this->prepareCacheId($this->_requestId);

        return $this;
    }

    /**
     * Refresh values of request ids
     *
     * Some parts of $this->_requestId and $this->_requestCacheId might be changed in runtime
     * E.g. we may not know about design package
     * But during cache save we need this data to be actual
     *
     * @return Enterprise_PageCache_Model_Processor
     */
    public function refreshRequestIds()
    {
        if (!$this->_designExceptionExistsInCache) {
            $this->_createRequestIds();
        }
        return $this;
    }

    /**
     * Get currently configured design package.
     * Depends on design exception rules configuration and browser user agent
     *
     * return string|bool
     */
    protected function _getDesignPackage()
    {
        $cacheInstance = Enterprise_PageCache_Model_Cache::getCacheInstance();
        $exceptions = $cacheInstance->load(self::DESIGN_EXCEPTION_KEY);
        $this->_designExceptionExistsInCache = $cacheInstance->getFrontend()->test(self::DESIGN_EXCEPTION_KEY);
        if (!$exceptions) {
            return false;
        }

        $exceptions = @unserialize($exceptions);
        if (!is_array($exceptions)) {
            return false;
        }

        if (isset($_COOKIE[Mage_Core_Model_Store::COOKIE_NAME])) {
            $storeIdentifier = $_COOKIE[Mage_Core_Model_Store::COOKIE_NAME];
        } else {
            $storeIdentifier = Mage::app()->getRequest()->getHttpHost() . Mage::app()->getRequest()->getBaseUrl();
        }
        if (!isset($exceptions[$storeIdentifier])) {
            return false;
        }

        $keys = array();
        foreach ($exceptions[$storeIdentifier] as $type => $exception) {
            $rule = @unserialize($exception);
            if (is_array($rule)) {
                $keys[] = Mage_Core_Model_Design_Package::getPackageByUserAgent($rule, $type);
            } else {
                $keys[] = '';
            }
        }
        return implode($keys, "|");
    }

    /**
     * Prepare page identifier
     *
     * @param string $id
     * @return string
     */
    public function prepareCacheId($id)
    {
        $cacheId = self::REQUEST_ID_PREFIX . md5($id . $this->_getScopeCode());
        return $cacheId;
    }

     /**
     * Get current scope code
     *
     * @return string
     */
    protected function _getScopeCode()
    {
        $params = Mage::registry('application_params');
        $scopeCode = '';
        if(isset($params['scope_code'])) {
            $scopeCode = $params['scope_code'];
        }
        return $scopeCode;
    }

    /**
     * Get HTTP request identifier
     *
     * @return string
     */
    public function getRequestId()
    {
        return $this->_requestId;
    }

    /**
     * Get page identifier for loading page from cache
     * @return string
     */
    public function getRequestCacheId()
    {
        return $this->_requestCacheId;
    }

    /**
     * Check if processor is allowed for current HTTP request.
     * Disable processing HTTPS requests and requests with "NO_CACHE" cookie
     *
     * @return bool
     */
    public function isAllowed()
    {
        if (!$this->_requestId) {
            return false;
        }
        if (isset($_COOKIE['NO_CACHE'])) {
            return false;
        }
        if (isset($_GET['no_cache'])) {
            return false;
        }
        if (isset($_GET[Mage_Core_Model_Session_Abstract::SESSION_ID_QUERY_PARAM])) {
            return false;
        }
        if (!Mage::app()->useCache('full_page')) {
            return false;
        }

        return true;
    }

    /**
     * Get page content from cache storage
     *
     * @param string $content
     * @return string|false
     */
    public function extractContent($content)
    {
        $cacheInstance = Enterprise_PageCache_Model_Cache::getCacheInstance();
        /*
         * Apply design change
         */
        $designChange = $cacheInstance->load($this->getRequestCacheId() . self::DESIGN_CHANGE_CACHE_SUFFIX);
        if ($designChange) {
            $designChange = unserialize($designChange);
            if (is_array($designChange) && isset($designChange['package']) && isset($designChange['theme'])) {
                $designPackage = Mage::getSingleton('core/design_package');
                $designPackage->setPackageName($designChange['package'])
                    ->setTheme($designChange['theme']);
            }
        }

        if (!$this->_designExceptionExistsInCache) {
            //no design exception value - error
            //must be at least empty value
            return false;
        }
        if (!$content && $this->isAllowed()) {
            $subprocessorClass = $this->getMetadata('cache_subprocessor');
            if (!$subprocessorClass) {
                return $content;
            }

            /*
             * @var Enterprise_PageCache_Model_Processor_Default
             */
            $subprocessor = new $subprocessorClass;
            $this->setSubprocessor($subprocessor);
            $cacheId = $this->prepareCacheId($subprocessor->getPageIdWithoutApp($this));

            $content = $cacheInstance->load($cacheId);

            if ($content) {
                if (function_exists('gzuncompress')) {
                    $content = gzuncompress($content);
                }
                $content = $this->_processContent($content);

                // restore response headers
                $responseHeaders = $this->getMetadata('response_headers');
                $response = Mage::app()->getResponse();
                if (is_array($responseHeaders)) {
                    $response->clearHeaders();
                    foreach ($responseHeaders as $header) {
                        $response->setHeader($header['name'], $header['value'], $header['replace']);
                    }
                }

                // renew recently viewed products
                $productId = $cacheInstance->load($this->getRequestCacheId() . '_current_product_id');
                $countLimit = $cacheInstance->load($this->getRecentlyViewedCountCacheId());
                if ($productId && $countLimit) {
                    Enterprise_PageCache_Model_Cookie::registerViewedProducts($productId, $countLimit);
                }
            }

        }
        return $content;
    }

    /**
     * Retrieve recently viewed count cache identifier
     *
     * @return string
     */
    public function getRecentlyViewedCountCacheId()
    {
        $cookieName = Mage_Core_Model_Store::COOKIE_NAME;
        return 'recently_viewed_count' . (isset($_COOKIE[$cookieName]) ? '_' . $_COOKIE[$cookieName] : '');
    }

    /**
     * Retrieve session info cache identifier
     *
     * @return string
     */
    public function getSessionInfoCacheId()
    {
        $cookieName = Mage_Core_Model_Store::COOKIE_NAME;
        return 'full_page_cache_session_info' . (isset($_COOKIE[$cookieName]) ? '_' . $_COOKIE[$cookieName] : '');
    }

    /**
     * Determine and process all defined containers.
     * Direct request to pagecache/request/process action if necessary for additional processing
     *
     * @param string $content
     * @return string|false
     */
    protected function _processContent($content)
    {
        $containers = $this->_processContainers($content);
        $isProcessed = empty($containers);
        // renew session cookie
        $sessionInfo = Enterprise_PageCache_Model_Cache::getCacheInstance()->load($this->getSessionInfoCacheId());

        if ($sessionInfo) {
            $sessionInfo = unserialize($sessionInfo);
            foreach ($sessionInfo as $cookieName => $cookieInfo) {
                if (isset($_COOKIE[$cookieName]) && isset($cookieInfo['lifetime'])
                    && isset($cookieInfo['path']) && isset($cookieInfo['domain'])
                    && isset($cookieInfo['secure']) && isset($cookieInfo['httponly'])
                ) {
                    $lifeTime = (0 == $cookieInfo['lifetime']) ? 0 : time() + $cookieInfo['lifetime'];
                    setcookie($cookieName, $_COOKIE[$cookieName], $lifeTime,
                        $cookieInfo['path'], $cookieInfo['domain'],
                        $cookieInfo['secure'], $cookieInfo['httponly']
                    );
                }
            }
        } else {
            $isProcessed = false;
        }

        $formKey = Enterprise_PageCache_Model_Cookie::getFormKeyCookieValue();
        if (!$formKey) {
            $formKey = Enterprise_PageCache_Helper_Data::getRandomString(16);
            Enterprise_PageCache_Model_Cookie::setFormKeyCookieValue($formKey);
        }

        Enterprise_PageCache_Helper_Form_Key::restoreFormKey($content, $formKey);

        /**
         * restore session_id in content whether content is completely processed or not
         */
        $sidCookieName = $this->getMetadata('sid_cookie_name');
        $sidCookieValue = $sidCookieName && isset($_COOKIE[$sidCookieName]) ? $_COOKIE[$sidCookieName] : '';

        // XSS vulnerability protection provided by htmlspcialchars call - escape & " ' < > chars
        Enterprise_PageCache_Helper_Url::restoreSid($content, htmlspecialchars($sidCookieValue, ENT_QUOTES));

        if ($isProcessed) {
            return $content;
        } else {
            Mage::register('cached_page_content', $content);
            Mage::register('cached_page_containers', $containers);
            Mage::app()->getRequest()
                ->setModuleName('pagecache')
                ->setControllerName('request')
                ->setActionName('process')
                ->isStraight(true);

            // restore original routing info
            $routingInfo = array(
                'aliases'              => $this->getMetadata('routing_aliases'),
                'requested_route'      => $this->getMetadata('routing_requested_route'),
                'requested_controller' => $this->getMetadata('routing_requested_controller'),
                'requested_action'     => $this->getMetadata('routing_requested_action')
            );

            Mage::app()->getRequest()->setRoutingInfo($routingInfo);
            return false;
        }
    }

    /**
     * Process Containers
     *
     * @param $content
     * @return array
     */
    protected function _processContainers(&$content)
    {
        $placeholders = array();
        preg_match_all(
            Enterprise_PageCache_Model_Container_Placeholder::HTML_NAME_PATTERN,
            $content, $placeholders, PREG_PATTERN_ORDER
        );
        $placeholders = array_unique($placeholders[1]);
        $containers = array();
        foreach ($placeholders as $definition) {
            $placeholder = new Enterprise_PageCache_Model_Container_Placeholder($definition);
            $container = $placeholder->getContainerClass();
            if (!$container) {
                continue;
            }

            $container = new $container($placeholder);
            $container->setProcessor($this);
            if (!$container->applyWithoutApp($content)) {
                $containers[] = $container;
            } else {
                preg_match($placeholder->getPattern(), $content, $matches);
                if (array_key_exists(1,$matches)) {
                    $containers = array_merge($this->_processContainers($matches[1]), $containers);
                    $content = preg_replace($placeholder->getPattern(), str_replace('$', '\\$', $matches[1]), $content);
                }
            }
        }
        return $containers;
    }

    /**
     * Associate tag with page cache request identifier
     *
     * @param array|string $tag
     * @return Enterprise_PageCache_Model_Processor
     */
    public function addRequestTag($tag)
    {
        if (!is_array($tag)) {
            $tag = array($tag);
        }
        foreach ($tag as $value) {
            if (!in_array($value, $this->_requestTags)) {
                $this->_requestTags[] = $value;
            }
        }
        return $this;
    }

    /**
     * Get cache request associated tags
     * @return array
     */
    public function getRequestTags()
    {
        return $this->_requestTags;
    }

    /**
     * Process response body by specific request
     *
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return Enterprise_PageCache_Model_Processor
     */
    public function processRequestResponse(Zend_Controller_Request_Http $request,
        Zend_Controller_Response_Http $response
    ) {
        // we should add original path info tag as another way we can't drop some entities from cron job
        $this->addRequestTag(Enterprise_PageCache_Helper_Url::prepareRequestPathTag($request->getOriginalPathInfo()));
        $cacheInstance = Enterprise_PageCache_Model_Cache::getCacheInstance();
        /**
         * Basic validation for request processing
         */
        if ($this->canProcessRequest($request)) {
            $processor = $this->getRequestProcessor($request);
            if ($processor && $processor->allowCache($request)) {
                $this->setMetadata('cache_subprocessor', get_class($processor));

                $cacheId = $this->prepareCacheId($processor->getPageIdInApp($this));
                $content = $processor->prepareContent($response);

                /**
                 * Replace all occurrences of session_id with unique marker
                 */
                Enterprise_PageCache_Helper_Url::replaceSid($content);
                Enterprise_PageCache_Helper_Form_Key::replaceFormKey($content);

                if (function_exists('gzcompress')) {
                    $content = gzcompress($content);
                }

                $contentSize = strlen($content);
                $currentStorageSize = (int) $cacheInstance->load(self::CACHE_SIZE_KEY);

                if (Mage::getStoreConfig(Enterprise_PageCache_Model_Processor::XML_PATH_CACHE_DEBUG)) {
                    $response->setBody(implode(', ', $this->getRequestTags()) . $response->getBody());
                }

                $maxSizeInBytes = Mage::getStoreConfig(self::XML_PATH_CACHE_MAX_SIZE) * 1024 * 1024;

                if ($currentStorageSize >= $maxSizeInBytes) {
                    Mage::app()->getCacheInstance()->invalidateType('full_page');
                    return $this;
                }

                $cacheInstance->save($content, $cacheId, $this->getRequestTags());

                $cacheInstance->save(
                    $currentStorageSize + $contentSize,
                    self::CACHE_SIZE_KEY,
                    $this->getRequestTags()
                );

                /*
                 * Save design change in cache
                 */
                $designChange = Mage::getSingleton('core/design');
                if ($designChange->getData()) {
                    $cacheInstance->save(
                        serialize($designChange->getData()),
                        $this->getRequestCacheId() . self::DESIGN_CHANGE_CACHE_SUFFIX,
                        $this->getRequestTags()
                    );
                }

                // save response headers
                $this->setMetadata('response_headers', $response->getHeaders());

                // save original routing info
                $this->setMetadata('routing_aliases', Mage::app()->getRequest()->getAliases());
                $this->setMetadata('routing_requested_route', Mage::app()->getRequest()->getRequestedRouteName());
                $this->setMetadata('routing_requested_controller',
                    Mage::app()->getRequest()->getRequestedControllerName());
                $this->setMetadata('routing_requested_action', Mage::app()->getRequest()->getRequestedActionName());

                $this->setMetadata('sid_cookie_name', Mage::getSingleton('core/session')->getSessionName());

                Mage::dispatchEvent('pagecache_processor_metadata_before_save', array('processor' => $this));

                $this->_saveMetadata();
            }

            if (isset($_GET[Mage_Core_Model_Session_Abstract::SESSION_ID_QUERY_PARAM])) {
                Mage::getSingleton('enterprise_pagecache/cookie')->updateCustomerCookies();
                Mage::getModel('enterprise_pagecache/observer')->updateCustomerProductIndex();

            }
        }
        return $this;
    }

    /**
     * Do basic validation for request to be cached
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function canProcessRequest(Zend_Controller_Request_Http $request)
    {
        $res = $this->isAllowed();
        $res = $res && Mage::app()->useCache('full_page');
        if ($request->getParam('no_cache')) {
            $res = false;
        }

        if ($res) {
            $maxDepth = Mage::getStoreConfig(self::XML_PATH_ALLOWED_DEPTH);
            $queryParams = $request->getQuery();
            unset($queryParams[Enterprise_PageCache_Model_Cache::REQUEST_MESSAGE_GET_PARAM]);
            $res = count($queryParams)<=$maxDepth;
        }
        if ($res) {
            $multicurrency = Mage::getStoreConfig(self::XML_PATH_CACHE_MULTICURRENCY);
            if (!$multicurrency && !empty($_COOKIE['currency'])) {
                $res = false;
            }
        }
        return $res;
    }

    /**
     * Get specific request processor based on request parameters.
     *
     * @param Zend_Controller_Request_Http $request
     * @return Enterprise_PageCache_Model_Processor_Default|false
     */
    public function getRequestProcessor(Zend_Controller_Request_Http $request)
    {
        if ($this->_requestProcessor === null) {
            $this->_requestProcessor = false;
            $configuration = Mage::getConfig()->getNode(self::XML_NODE_ALLOWED_CACHE);
            if ($configuration) {
                $configuration = $configuration->asArray();
            }
            $module = $request->getModuleName();
            $action = $request->getActionName();
            if (strtolower($action) == self::NOT_FOUND_ACTION && isset($configuration['_no_route'])) {
                $model = $configuration['_no_route'];
            } elseif (isset($configuration[$module])) {
                $model = $configuration[$module];
                $controller = $request->getControllerName();
                if (is_array($configuration[$module]) && isset($configuration[$module][$controller])) {
                    $model = $configuration[$module][$controller];
                    if (is_array($configuration[$module][$controller])
                            && isset($configuration[$module][$controller][$action])) {
                        $model = $configuration[$module][$controller][$action];
                    }
                }
            }
            if (isset($model) && is_string($model)) {
                $this->_requestProcessor = Mage::getModel($model);
            }
        }
        return $this->_requestProcessor;
    }

    /**
     * Set metadata value for specified key
     *
     * @param string $key
     * @param string $value
     *
     * @return Enterprise_PageCache_Model_Processor
     */
    public function setMetadata($key, $value)
    {
        $this->_loadMetadata();
        $this->_metaData[$key] = $value;
        return $this;
    }

    /**
     * Get metadata value for specified key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getMetadata($key)
    {
        $this->_loadMetadata();
        return (isset($this->_metaData[$key])) ? $this->_metaData[$key] : null;
    }

    /**
     * Return current page base url
     *
     * @return string
     */
    protected function _getFullPageUrl()
    {
        $uri = false;
        /**
         * Define server HTTP HOST
         */
        if (isset($_SERVER['HTTP_HOST'])) {
            $uri = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $uri = $_SERVER['SERVER_NAME'];
        }

        /**
         * Define request URI
         */
        if ($uri) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $uri.= $_SERVER['REQUEST_URI'];
            } elseif (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL'])) {
                $uri.= $_SERVER['UNENCODED_URL'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
                $uri.= $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $uri.= $_SERVER['QUERY_STRING'];
                }
            }
        }
        return $uri;
    }


    /**
     * Save metadata for cache in cache storage
     */
    protected function _saveMetadata()
    {
        Enterprise_PageCache_Model_Cache::getCacheInstance()->save(
            serialize($this->_metaData),
            $this->getRequestCacheId() . self::METADATA_CACHE_SUFFIX,
            $this->getRequestTags()
            );
    }

    /**
     * Load cache metadata from storage
     */
    protected function _loadMetadata()
    {
        if ($this->_metaData === null) {
            $cacheMetadata = Enterprise_PageCache_Model_Cache::getCacheInstance()
                ->load($this->getRequestCacheId() . self::METADATA_CACHE_SUFFIX);
            if ($cacheMetadata) {
                $cacheMetadata = unserialize($cacheMetadata);
            }
            $this->_metaData = (empty($cacheMetadata) || !is_array($cacheMetadata)) ? array() : $cacheMetadata;
        }
    }

    /**
     * Set subprocessor
     *
     * @param mixed $subprocessor
     */
    public function setSubprocessor($subprocessor)
    {
        $this->_subprocessor = $subprocessor;
    }

    /**
     * Get subprocessor
     *
     * @return mixed
     */
    public function getSubprocessor()
    {
        return $this->_subprocessor;
    }
}
