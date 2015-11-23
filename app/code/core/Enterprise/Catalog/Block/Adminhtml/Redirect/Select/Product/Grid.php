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
 * Adminhtml URL redirect product grid block
 *
 * @category   Enterprise
 * @package    Enterprise_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Block_Adminhtml_Redirect_Select_Product_Grid
    extends Mage_Adminhtml_Block_Catalog_Product_Grid
{

    /**
     * For redirect grid the following fields are not needed:
     * - visibility
     * - price
     *
     * @return Enterprise_Catalog_Block_Adminhtml_Redirect_Select_Product_Grid
     */
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if ($store->getId()) {
            $collection->addStoreFilter($store)
                ->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner',
                Mage_Core_Model_App::ADMIN_STORE_ID)
                ->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId())
                ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
        } else {
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        }

        $this->setCollection($collection);

        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        return $this;
    }

    /**
     * Disable massaction
     *
     * @return Enterprise_Catalog_Block_Adminhtml_Redirect_Select_Product_Grid
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Prepare columns layout
     *
     * @return Enterprise_Catalog_Block_Adminhtml_Redirect_Select_Product_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => $this->__('ID'),
            'width'  => 50,
            'index'  => 'entity_id',
        ))->addColumn('name', array(
            'header' => $this->__('Name'),
            'index'  => 'name',
        ))->addColumn('sku', array(
            'header' => $this->__('SKU'),
            'width'  => 80,
            'index'  => 'sku',
        ))->addColumn('status', array(
            'header'  => $this->__('Status'),
            'width'   => 50,
            'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        return $this;
    }

    /**
     * Get url for dispatching grid ajax requests
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGrid', array('_current' => true));
    }

    /**
     * Get row url
     *
     * @param Mage_Catalog_Model_Product $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/select', array(
            'product' => $row->getId(),
            'type'    => 'category'
        ));
    }
}
