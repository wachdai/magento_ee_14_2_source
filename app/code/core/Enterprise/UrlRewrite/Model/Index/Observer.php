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
 * Url Rewrite Redirect observer
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Model_Index_Observer
{
    /**
     * Update on save redirect url rewrite xml path.
     *
     * @var string
     */
    const XML_PATH_REDIRECT_URL_SUFFIX_UPDATE_ON_SAVE = 'default/index_management/index_options/redirect_url_rewrite';

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Initialize model
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        $this->_config = !empty($args['config']) ? $args['config'] : Mage::getConfig();
    }

    /**
     * Refresh url rewrite for given redirect
     *
     * @param Varien_Event_Observer $observer
     */
    public function refreshRedirectUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveRedirectUrlRewriteFlag()) {
            return;
        }

        /** @var $redirect Enterprise_UrlRewrite_Model_Redirect */
        $redirect = $observer->getEvent()->getRedirect();
        if (!is_object($redirect) || !$redirect->getId()) {
            return;
        }

        /** @var $client Enterprise_Mview_Model_Client */
        $client = $this->_factory->getModel('enterprise_mview/client')->init('enterprise_url_rewrite_redirect');
        $client->execute('enterprise_urlrewrite/index_action_url_rewrite_redirect_refresh_row', array(
            'redirect_id' => $redirect->getId()
        ));
    }

    /**
     * Process shell reindex url redirect refresh event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_UrlRewrite_Model_Index_Observer
     */
    public function processShellUrlRedirectReindexEvent(Varien_Event_Observer $observer)
    {
        $client = $this->_getClient('enterprise_url_rewrite_redirect');
        $client->execute('enterprise_urlrewrite/index_action_url_rewrite_redirect_refresh');

        return $this;
    }

    /**
     * Retrieve client instance
     *
     * @param string $metadataTableName
     * @return Enterprise_Mview_Model_Client
     */
    protected function _getClient($metadataTableName)
    {
        /** @var $client Enterprise_Mview_Model_Client */
        $client = $this->_factory->getModel('enterprise_mview/client', array(array('factory' => $this->_factory)));
        $client->init($metadataTableName);
        return $client;
    }

    /**
     * Check whether product url rewrite should be updated once save operation is triggered.
     *
     * @return bool
     */
    protected function _isUpdateOnSaveRedirectUrlRewriteFlag()
    {
        return (bool)(string)$this->_config->getNode(self::XML_PATH_REDIRECT_URL_SUFFIX_UPDATE_ON_SAVE);
    }
}
