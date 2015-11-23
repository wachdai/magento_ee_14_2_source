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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise Catalog URL index observer
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Observer_Url
{
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
    }

    /**
     * Process shell reindex category url rewrite refresh event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Catalog_Model_Index_Observer_Url
     */
    public function processShellUrlCategoryReindexEvent(Varien_Event_Observer $observer)
    {
        $client = $this->_getClient('enterprise_url_rewrite_category');
        $client->execute('enterprise_catalog/index_action_url_rewrite_category_refresh');

        return $this;
    }

    /**
     * Process shell reindex product url rewrite refresh event
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Catalog_Model_Index_Observer_Url
     */
    public function processShellUrlProductReindexEvent(Varien_Event_Observer $observer)
    {
        $client = $this->_getClient('enterprise_url_rewrite_product');
        $client->execute('enterprise_catalog/index_action_url_rewrite_product_refresh');

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
}
