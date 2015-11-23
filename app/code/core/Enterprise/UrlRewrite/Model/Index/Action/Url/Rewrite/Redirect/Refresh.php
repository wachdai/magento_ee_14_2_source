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
class Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_Redirect_Refresh
    extends Enterprise_UrlRewrite_Model_Index_Action_Url_Rewrite_RefreshAbstract
    implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Initialize unique value, relation columns and relation
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);

        $this->_relationColumns = array('redirect_id', 'url_rewrite_id');
        $this->_relationTableName = $this->_getTable('enterprise_urlrewrite/redirect_rewrite');
    }

    /**
     * Prepares url rewrite select query
     *
     * @return Varien_Db_Select
     */
    protected function _getUrlRewriteSelectSql()
    {
        $caseSql = $this->_connection->getCaseSql('',
            array('ur.inc IS NULL OR '
                . $this->_connection->quoteIdentifier('m.value') . ' = 1' => new Zend_Db_Expr("''")),
            $this->_connection->getConcatSql(array("'-'", 'ur.inc'))
        );

        $sRequestPath = $this->_connection->getConcatSql(array('r.identifier', $caseSql));

        return $this->_connection->select()
            ->from(array('r' => $this->_getTable('enterprise_urlrewrite/redirect')),
            array(
                'request_path'  => new Zend_Db_Expr($sRequestPath),
                'target_path'   => 'r.target_path',
                'guid'          => new Zend_Db_Expr($this->_connection->quote($this->_uniqueIdentifier)),
                'is_system'     => new Zend_Db_Expr(0),
                'identifier'    => new Zend_Db_Expr($sRequestPath),
                'value_id'      => 'r.redirect_id',
                'store_id'      => 'r.store_id',
                'entity_type'   => new Zend_Db_Expr(Enterprise_UrlRewrite_Model_Redirect::URL_REWRITE_ENTITY_TYPE)
            ))
            ->joinLeft(array('ur' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                'ur.identifier = r.identifier AND ur.store_id = r.store_id AND ur.entity_type = ' .
                    new Zend_Db_Expr(Enterprise_UrlRewrite_Model_Redirect::URL_REWRITE_ENTITY_TYPE), array())
            ->joinLeft(array('m' => $this->_getTable('enterprise_index/multiplier')),
            'ur.identifier IS NOT NULL', array());
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
            array('redirect_id' => 'r.redirect_id', 'url_rewrite_id'))
            ->join(array('r' => $this->_getTable('enterprise_urlrewrite/redirect')),
            'r.redirect_id = ur.value_id', array())
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
                array('r' => $this->_getTable('enterprise_urlrewrite/redirect_rewrite')),
                'r.url_rewrite_id = ur.url_rewrite_id', array()
            )
            ->where('ur.is_system = ?', 0)
            ->where('ur.entity_type = ?', Enterprise_UrlRewrite_Model_Redirect::URL_REWRITE_ENTITY_TYPE);
    }
}
