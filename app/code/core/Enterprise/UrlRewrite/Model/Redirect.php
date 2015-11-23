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
 * @package     Enterprise_UrlRewrite
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * UrlRewrite redirect model
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Model_Redirect extends Mage_Core_Model_Abstract
{
    /**
     * Url Rewrite Entity Type
     */
    const URL_REWRITE_ENTITY_TYPE = 1;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'redirect';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'redirect';

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('enterprise_urlrewrite/redirect');
    }

    /**
     * Validate identifier and target path
     *
     * @return bool
     */
    public function validate()
    {
        return Mage::helper('core/url_rewrite')->validateRequestPath($this->getIdentifier());
    }

    /**
     * Make validation before saving
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $this->validate();
        return parent::_beforeSave();
    }

    /**
     * Load rewrite object by request path
     *
     * @param string $requestPath
     * @param int $storeId
     * @return Enterprise_UrlRewrite_Model_Redirect
     */
    public function loadByRequestPath($requestPath, $storeId)
    {
        $this->setId(null);
        $this->_getResource()->loadByRequestPath($this, $requestPath, $storeId);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

    /**
     * Detect circular redirects
     *
     * @return bool
     */
    public function isCircular($circularChain = array())
    {
        $circularChain[] = $this->getIdentifier();

        $redirect = Mage::getModel('enterprise_urlrewrite/redirect')->loadByRequestPath(
            $this->getTargetPath(), $this->getStoreId()
        );
        if (!$redirect->getId()) {
            return false;
        }
        if (in_array($redirect->getTargetPath(), $circularChain)) {
            return true;
        }

        return $redirect->isCircular($circularChain);
    }

    /**
     * Check whether redirect record exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->_getResource()->exists($this);
    }
}
