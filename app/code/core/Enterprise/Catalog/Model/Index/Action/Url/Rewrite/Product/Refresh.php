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
 * Url Rewrite Product Refresh Action
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Product_Refresh
    extends Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_RefreshAbstract
    implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Base target path.
     */
    const BASE_TARGET_PATH = 'catalog/product/view/id/';

    /**
     * Initialize relation columns and relation table name
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);

        $this->_relationColumns = array('product_id', 'store_id', 'url_rewrite_id');
        $this->_relationTableName = $this->_getTable('enterprise_catalog/product');
    }

    /**
     * Prepares url rewrite select query
     *
     * @return Varien_Db_Select
     */
    protected function _getUrlRewriteSelectSql()
    {
        $sTargetPath = $this->_connection->getConcatSql(array("'" . self::BASE_TARGET_PATH . "'", 'uk.entity_id'));

        return $this->_connection->select()
            ->from(array('uk' => $this->_getTable(array('catalog/product', 'url_key'))),
                array(
                    'request_path'  => 'uk.value',
                    'target_path'   => new Zend_Db_Expr($sTargetPath),
                    'guid'          => new Zend_Db_Expr($this->_connection->quote($this->_uniqueIdentifier)),
                    'is_system'     => new Zend_Db_Expr(1),
                    'identifier'    => 'uk.value',
                    'value_id'      => 'uk.value_id',
                    'store_id'      => 'uk.store_id',
                    'entity_type'   => new Zend_Db_Expr(Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE),
                ));
    }

    /**
     * Prepares refresh relation select query
     *
     * @return Varien_Db_Select
     */
    protected function _getRefreshRelationSelectSql()
    {
        return $this->_connection->select()
            ->from(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                array('product_id' => 'uk.entity_id', 'uk.store_id', 'url_rewrite_id'))
            ->join(array('uk' => $this->_getTable(array('catalog/product', 'url_key'))),
                'uk.value_id = ur.value_id', array())
            ->where('guid = ?', $this->_uniqueIdentifier);
    }

    /**
     * Returns select query for deleting old url rewrites
     *
     * @return Varien_Db_Select
     */
    protected function _getCleanOldUrlRewriteSelect()
    {
        return $this->_connection->select()
            ->from(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')))
            ->join(
                array('rp' => $this->_getTable('enterprise_catalog/product')),
                'rp.url_rewrite_id = ur.url_rewrite_id', array()
            )
            ->where('ur.entity_type = ?', Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE);
    }
}
