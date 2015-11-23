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
 * @package     Enterprise_Bundle
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Bundle Stock Status Indexer Resource Model
 *
 * @category    Enterprise
 * @package     Enterprise_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Bundle_Model_Resource_Indexer_Stock extends Mage_Bundle_Model_Resource_Indexer_Stock
{
    /**
     * Clean temporary bundle options stock data
     *
     * @return Mage_Bundle_Model_Resource_Indexer_Stock
     */
    protected function _cleanBundleOptionStockData()
    {
        if ($this->_getWriteAdapter()->getTransactionLevel() == 0) {
            $this->_getWriteAdapter()->truncateTable($this->_getBundleOptionTable());
        } else {
            $this->_getWriteAdapter()->delete($this->_getBundleOptionTable());
        }
        return $this;
    }
}
