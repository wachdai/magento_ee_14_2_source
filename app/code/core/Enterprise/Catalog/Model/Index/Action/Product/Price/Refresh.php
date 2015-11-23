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
 * Full refresh price index
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Product_Price_Refresh
    extends Enterprise_Catalog_Model_Index_Action_Product_Price_Abstract
{
    /**
     * Refresh all entities
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Price_Refresh
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        $this->_validate();
        try {
            $this->_getCurrentVersionId();
            $this->_metadata->setInProgressStatus()->save();
            $this->_reindexAll();
            $this->_setChangelogValid();
            Mage::dispatchEvent('catalog_product_price_full_reindex');
        } catch (Exception $e) {
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }

    /**
     * Reindex all
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Price_Refresh
     */
    protected function _reindexAll()
    {
        $this->_useIdxTable(true);
        $this->_emptyTable($this->_getIdxTable());
        $this->_prepareWebsiteDateTable();
        $this->_prepareTierPriceIndex();
        $this->_prepareGroupPriceIndex();

        $indexers = $this->_getTypeIndexers();
        foreach ($indexers as $indexer) {
            /** @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Price_Interface */
            $indexer->reindexAll();
        }
        $this->_syncData();

        return $this;
    }
}
