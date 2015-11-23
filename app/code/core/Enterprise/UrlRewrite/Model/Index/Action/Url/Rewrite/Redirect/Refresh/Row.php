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
 * Url Rewrite Redirect Refresh Action
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_Redirect_Refresh_Row
    extends Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_Redirect_Refresh
{
    /**
     * Product id
     *
     * @var int
     */
    protected $_redirectId;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'redirect_id' int
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->_redirectId = !empty($args['redirect_id']) ? (int)$args['redirect_id'] : null;
    }

    /**
     * Execute refresh operation.
     *  - clean redirect url rewrites
     *  - refresh redirect url rewrites
     *  - refresh redirect to url rewrite relations
     *
     * @return Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_Redirect_Refresh_Row
     * @throws Enterprise_Index_Exception
     */
    public function execute()
    {
        if (!$this->_redirectId) {
            return $this;
        }

        $this->_connection->beginTransaction();
        try {
            $this->_cleanOldUrlRewrite();
            $this->_refreshUrlRewrite();
            $this->_refreshRelation();
            $this->_connection->commit();
        } catch (Exception $e) {
            $this->_connection->rollBack();
            throw new Enterprise_Index_Exception($e->getMessage(), $e->getCode());
        }
        return $this;
    }

    /**
     * Prepares url rewrite select query
     *
     * @return Varien_Db_Select
     */
    protected function _getUrlRewriteSelectSql()
    {
        $select = parent::_getUrlRewriteSelectSql();
        return $this->_addRedirectIdWhere($select);
    }

    /**
     * Prepares refresh relation select query for given product_id
     *
     * @return Varien_Db_Select
     */
    protected function _getRefreshRelationSelectSql()
    {
        $select = parent::_getRefreshRelationSelectSql();
        return $this->_addRedirectIdWhere($select);
    }

    /**
     * Returns select query for deleting old url rewrites.
     *
     * @return Varien_Db_Select
     */
    protected function _getCleanOldUrlRewriteSelect()
    {
        $select = parent::_getCleanOldUrlRewriteSelect();
        return $this->_addRedirectIdWhere($select);
    }

    /**
     * Add redirect_id value in where clause
     *
     * @param Varien_Db_Select $select
     * @return Varien_Db_Select
     */
    protected function _addRedirectIdWhere(Varien_Db_Select $select)
    {
        return $select->where('r.redirect_id = ?', $this->_redirectId);
    }
}
