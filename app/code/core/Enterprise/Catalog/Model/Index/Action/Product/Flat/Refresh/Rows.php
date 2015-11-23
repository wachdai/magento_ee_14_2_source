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
 * Flat product index refresh action class
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh_Rows
    extends Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh_Changelog
{
    /**
     * Product ids for udpate
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'factory' Enterprise_Mview_Model_Factory
     *  - 'value' array
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        if (isset($args['value'])) {
            $this->_productIds = $args['value'];
        }
    }

    /**
     * Method deletes old row in the mview table and insert new one from view.
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh_Rows
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_isFlatIndexerEnabled()) {
            return $this;
        }
        $this->_validate();
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            $idsBatches = array_chunk($this->_productIds, Mage::helper('enterprise_index')->getBatchSize());
            foreach ($idsBatches as $changedIds) {
                $this->_reindex($store->getId(), $changedIds);
            }
        }

        Mage::dispatchEvent('catalog_product_flat_partial_reindex', array('product_ids' => $this->_productIds));
        return $this;
    }

    /**
     * Validates value
     *
     * @return Enterprise_Catalog_Model_Index_Action_Product_Flat_Refresh_Rows
     * @throws Enterprise_Index_Model_Action_Exception
     */
    protected function _validate()
    {
        if (!is_array($this->_productIds) || empty($this->_productIds)) {
            throw new Enterprise_Index_Model_Action_Exception('Bad value was supplied.');
        }
        return $this;
    }
}
