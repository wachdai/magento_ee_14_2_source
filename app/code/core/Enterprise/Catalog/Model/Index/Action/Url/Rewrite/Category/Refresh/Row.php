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
 * Url Rewrite Category Refresh Row Action
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh_Row
    extends Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh_Changelog
{
    /**
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->_categoryId = !empty($args['category_id']) ? (int)$args['category_id'] : null;
    }

    /**
     * Returns list of changed Ids
     *
     * @return array
     */
    protected function _getChangedIds()
    {
        $categoryIds = array();
        if ($this->_categoryId) {
            $categoryIds[] = $this->_categoryId;
        }

        return $categoryIds;
    }

    /**
     * Returns select query for deleting old url rewrites.
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.02
     */
    protected function _getCleanOldUrlRewriteSelect()
    {
        $select = parent::_getCleanOldUrlRewriteSelect();
        $select->where('rc.category_id = ?', $this->_categoryId);
        return $select;
    }

    /**
     * Prepares url rewrite select query
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.02
     */
    protected function _getUrlRewriteSelectSql()
    {
        $select = parent::_getUrlRewriteSelectSql();
        $select->where('uk.entity_id = ?', $this->_categoryId);
        return $select;
    }

    /**
     * Prepares refresh relation select query for given category_id
     *
     * @return Varien_Db_Select
     * @deprecated since 1.13.02
     */
    protected function _getRefreshRelationSelectSql()
    {
        $select = parent::_getRefreshRelationSelectSql();
        $select->where('uk.entity_id = ?', $this->_categoryId);
        return $select;
    }
}
