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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Adminhtml customer cart items grid block
 */
class Enterprise_GiftRegistry_Block_Adminhtml_Customer_Edit_Cart
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('giftregistry_customer_cart_grid');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        $quote = Mage::getModel('sales/quote');
        $quote->setWebsite(Mage::app()->getWebsite($this->getEntity()->getWebsiteId()));
        $quote->loadByCustomer(Mage::getModel('customer/customer')->load($this->getEntity()->getCustomerId()));

        $collection = ($quote) ? $quote->getItemsCollection(false) : new Varien_Data_Collection();
        $collection->addFieldToFilter('parent_item_id', array('null' => true));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Product ID'),
            'index'  => 'product_id',
            'type'   => 'number',
            'width'  => '100px',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Product Name'),
            'index' => 'name',
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Product SKU'),
            'index' => 'sku',
            'width' => '200px',
        ));

        $this->addColumn('price', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Price'),
            'index' => 'price',
            'type'  => 'currency',
            'width' => '120px',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Quantity'),
            'index' => 'qty',
            'type'  => 'number',
            'width' => '120px',
        ));

        $this->addColumn('total', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Total'),
            'index' => 'row_total',
            'type'  => 'currency',
            'width' => '120px',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action options for this grid
     *
     * @return Enterprise_GiftRegistry_Block_Adminhtml_Customer_Edit_Cart
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('products');
        $this->getMassactionBlock()->addItem('add', array(
            'label'    => Mage::helper('enterprise_giftregistry')->__('Add to Gift Registry'),
            'url'      => $this->getUrl('*/*/add', array('id' => $this->getEntity()->getId())),
            'confirm'  => Mage::helper('enterprise_giftregistry')->__('Are you sure you want to add these products?')
        ));

        return $this;
    }

    /**
     * Return grid row url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }

    /**
     * Return gift registry entity object
     *
     * @return Enterprise_GiftRegistry_Model_Entity
     */
    public function getEntity()
    {
        return Mage::registry('current_giftregistry_entity');
    }
}
