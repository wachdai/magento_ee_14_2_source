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
 * @package     Enterprise_Checkout
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Accordion grid for catalog salable products
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Products
    extends Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Abstract
{
    /**
     * Block initializing, grid parameters
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('source_products');
        $this->setDefaultSort('entity_id');
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setHeaderText(Mage::helper('enterprise_checkout')->__('Products'));
    }

    /**
     * Return custom object name for js grid
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return 'productsGrid';
    }

    /**
     * Return items collection
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function getItemsCollection()
    {
        if (!$this->hasData('items_collection')) {
            $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->setStore($this->_getStore())
                ->addAttributeToSelect($attributes)
                ->addAttributeToSelect('sku')
                ->addAttributeToFilter(
                    'type_id',
                    array_keys(
                        Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()
                    )
                )->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->addStoreFilter($this->_getStore());
            Mage::getSingleton('cataloginventory/stock_status')->addIsInStockFilterToCollection($collection);
            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
            $this->setData('items_collection', $collection);
        }
        return $this->getData('items_collection');
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('enterprise_checkout')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('enterprise_checkout')->__('Product Name'),
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_product',
            'index'     => 'name'
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('enterprise_checkout')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('enterprise_checkout')->__('Price'),
            'type'      => 'currency',
            'column_css_class' => 'price',
            'currency_code' => $this->_getStore()->getCurrentCurrencyCode(),
            'rate' => $this->_getStore()->getBaseCurrency()->getRate($this->_getStore()->getCurrentCurrencyCode()),
            'index'     => 'price',
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_price'
        ));

        $this->_addControlColumns();

        return $this;
    }

    /**
     * Custom products grid search callback
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getChild('search_button')->setOnclick('checkoutObj.searchProducts()');
        return $this;
    }

    /**
     * Search by selected products
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (!$productIds) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } elseif($productIds) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Return array of selected product ids from request
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        if ($this->getRequest()->getPost('source')) {
            $source = Mage::helper('core')->jsonDecode($this->getRequest()->getPost('source'));
            if (isset($source['source_products']) && is_array($source['source_products'])) {
                return array_keys($source['source_products']);
            }
        }
        return false;
    }

    /**
     * Return grid URL for sorting and filtering
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/products', array('_current'=>true));
    }

    /**
     * Add columns with controls to manage added products and their quantity
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _addControlColumns()
    {
        parent::_addControlColumns();
        $this->getColumn('in_products')->setHeader(" ");
    }

    /*
     * Add custom options to product collection
     *
     * return Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Products
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addOptionsToResult();
        return parent::_afterLoadCollection();
    }
}
