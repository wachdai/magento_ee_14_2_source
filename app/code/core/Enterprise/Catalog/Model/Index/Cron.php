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
 * Index cron class
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @deprecated  deprecated since Magento version 1.13.02
 */
class Enterprise_Catalog_Model_Index_Cron extends Enterprise_Index_Model_Cron
{
    /**
     * Refresh url rewrites for category and product entities.
     */
    public function refreshUrlRewrite()
    {
        /** @var $client Enterprise_Mview_Model_Client */
        $client = $this->_getClient();
        try {
            $this->_refreshCategoryUrlRewrite($client);
            $this->_refreshProductUrlRewrite($client);
        } catch (Exception $e) {
            $this->_logger->logException($e);
        }
    }

    /**
     * Refresh categories url rewrites by changelog
     *
     * @param Enterprise_Mview_Model_Client $client
     * @return Enterprise_Index_Model_Cron
     */
    protected function _refreshCategoryUrlRewrite(Enterprise_Mview_Model_Client $client)
    {
        $client->init('enterprise_url_rewrite_category');
        $client->execute('enterprise_catalog/index_action_url_rewrite_category_refresh_changelog');
        return $this;
    }

    /**
     * Refresh products url rewrites by changelog
     *
     * @param Enterprise_Mview_Model_Client $client
     * @return Enterprise_Index_Model_Cron
     */
    protected function _refreshProductUrlRewrite(Enterprise_Mview_Model_Client $client)
    {
        $client->init('enterprise_url_rewrite_product');
        $client->execute('enterprise_catalog/index_action_url_rewrite_product_refresh_changelog');
        return $this;
    }
}
