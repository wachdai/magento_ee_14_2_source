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
class Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Product_Refresh_Row
    extends Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Product_Refresh_Changelog
{
    /**
     * Product id
     *
     * @var int
     */
    protected $_productId;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'product_id' int
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        $this->_productId = $args['product_id'];
    }

    /**
     * Execute refresh operation.
     *  - clean product url rewrites
     *  - refresh product url rewrites
     *  - refresh product to url rewrite relations
     *
     * @return Enterprise_Mview_Model_Action_Interface
     * @throws Enterprise_Index_Exception
     */
    public function execute()
    {
        if (!$this->_productId) {
            return $this;
        }

        $this->_connection->beginTransaction();
        try {
            $this->_cleanOldUrlRewrite();
            $this->_refreshUrlRewrite();
            $this->_refreshRelation();
            $this->_connection->commit();
            $this->_flushCache();
        } catch (Exception $e) {
            $this->_connection->rollBack();
            throw new Enterprise_Index_Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * Returns list of changed Ids
     *
     * @return array
     */
    protected function _getChangedIds()
    {
        return $this->_changedIds = array($this->_productId);
    }
}
