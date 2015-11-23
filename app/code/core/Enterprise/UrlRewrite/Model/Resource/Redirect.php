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
 * UrlRewrite redirect resource model
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Model_Resource_Redirect extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('enterprise_urlrewrite/redirect', 'redirect_id');
    }

    /**
     * Load rewrite object by request path
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $requestPath
     * @param int $storeId
     * @return Enterprise_UrlRewrite_Model_Url_Rewrite
     */
    public function loadByRequestPath(Mage_Core_Model_Abstract $object, $requestPath, $storeId)
    {
        $orSelect = $this->_getReadAdapter()->select();
        $orWhere = $orSelect->where('url_rewrite.store_id = ?', $storeId)
            ->orWhere('url_rewrite.store_id = 0')
            ->getPart(Varien_Db_Select::SQL_WHERE);

        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()))
            ->join(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite.request_path = ' . $this->_getReadAdapter()->quote($requestPath),
                array('request_path', 'url_rewrite_id', 'is_system')
            )
            ->join(array('rewrite_relation' => $this->getTable('enterprise_urlrewrite/redirect_rewrite')),
                'rewrite_relation.url_rewrite_id = url_rewrite.url_rewrite_id', array()
            )->where('main_table.redirect_id = rewrite_relation.redirect_id')
            ->where(implode(' ', $orWhere))
            ->order('url_rewrite.store_id ' . Varien_Db_Select::SQL_DESC);

        $result = $this->_getReadAdapter()->fetchRow($select);
        if (!empty($result)) {
            $object->setData($result);
        }

        return $this;
    }

    /**
     * Check whether redirect exists.
     *
     * @param Varien_Object $redirect
     * @return bool
     */
    public function exists(Varien_Object $redirect)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array(new Zend_Db_Expr('COUNT(*)')))
            ->where('identifier = ?', $redirect->getIdentifier())
            ->where('store_id = ?', $redirect->getStoreId());

        return (bool)$this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Get redirect by rewrite id
     *
     * @param int $rewriteId
     * @return array
     */
    public function getRedirectByRewriteId($rewriteId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('rw' => $this->getTable('enterprise_urlrewrite/url_rewrite')), array())
            ->joinInner(
                array('rr' => $this->getTable('enterprise_urlrewrite/redirect_rewrite')),
                'rr.url_rewrite_id = rw.url_rewrite_id',
                array('')
            )
            ->joinInner(
                array('r' => $this->getTable('enterprise_urlrewrite/redirect')),
                'rr.redirect_id = r.redirect_id'
            )
            ->where('rw.url_rewrite_id = ?', $rewriteId);
        return $this->_getReadAdapter()->fetchRow($select);
    }

    /**
     * Deletes all redirects related to given categories
     * @param array $categoryIds
     * @return int number of affected rows
     */
    public function deleteByCategoryIds(array $categoryIds)
    {
        if (!count($categoryIds)) {
            return 0;
        }
        return $this->_getWriteAdapter()->delete($this->getMainTable(), array('category_id IN (?)' => $categoryIds));
    }

    /**
     * Deletes all redirects related to given products
     * @param array $productIds
     * @return int number of affected rows
     */
    public function deleteByProductIds(array $productIds)
    {
        if (!count($productIds)) {
            return 0;
        }
        return $this->_getWriteAdapter()->delete($this->getMainTable(), array('product_id IN (?)' => $productIds));
    }
}
