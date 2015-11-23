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
 * Abstract class for accordion grids
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Abstract extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Collection field name for using in controls
     * @var string
     */
    protected $_controlFieldName = 'entity_id';

    /**
     * Javascript list type name for this grid
     */
    protected $_listType = 'product_to_add';

    /**
     * Url to configure this grid's items
     */
    protected $_configureRoute = '*/checkout/configureProductToAdd';

    /**
     * Initialize Grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setRowClickCallback('checkoutObj.productGridRowClick.bind(checkoutObj)');
        $this->setCheckboxCheckCallback('checkoutObj.productGridCheckboxCheck.bind(checkoutObj)');
        $this->setRowInitCallback('checkoutObj.productGridRowInit.bind(checkoutObj)');
    }

    /**
     * Workaround for displaying empty grid when no items found
     *
     * @return bools
     */
    public function getIsCollapsed()
    {
        return ($this->getItemsCount() == 0);
    }

    /**
     * Return items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        $collection = $this->getItemsCollection();
        if ($collection) {
            return count($collection->getItems());
        }
        return 0;
    }

    /**
     * Return items collection
     *
     * @return Varien_Data_Collection
     */
    public function getItemsCollection()
    {
        return new Varien_Data_Collection();
    }

    /**
     * Prepare collection for grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->getItemsCollection());
        return parent::_prepareCollection();
    }

    /**
     * Returns special renderer for price column content
     *
     * @return null|string
     */
    protected function _getPriceRenderer()
    {
        return null;
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_name', array(
            'header'    => Mage::helper('enterprise_checkout')->__('Product name'),
            'renderer'  => 'enterprise_checkout/adminhtml_manage_grid_renderer_product',
            'index'     => 'name',
            'sortable'  => false
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('enterprise_checkout')->__('Price'),
            'renderer'  => $this->_getPriceRenderer(),
            'align'     => 'right',
            'type'      => 'currency',
            'column_css_class' => 'price',
            'currency_code' => $this->_getStore()->getCurrentCurrencyCode(),
            'rate'      => $this->_getStore()->getBaseCurrency()->getRate($this->_getStore()->getCurrentCurrencyCode()),
            'index'     => 'price',
            'sortable'  => false
        ));

        $this->_addControlColumns();

        return parent::_prepareColumns();
    }

    /**
     * Add columns with controls to manage added products and their quantity
     *
     * @return Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Abstract
     */
    protected function _addControlColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'field_name'=> $this->getId() ? $this->getId() : 'source_product',
            'align'     => 'center',
            'index'     => $this->_controlFieldName,
            'use_index' => true,
            'sortable'  => false
        ));

        $this->addColumn('qty', array(
            'sortable'  => false,
            'header'    => Mage::helper('enterprise_checkout')->__('Qty To Add'),
            'renderer'  => 'adminhtml/sales_order_create_search_grid_renderer_qty',
            'name'      => 'qty',
            'inline_css'=> 'qty',
            'align'     => 'right',
            'type'      => 'input',
            'validate_class' => 'validate-number',
            'index'     => 'qty',
            'width'     => '1',
        ));

        return $this;
    }

    /**
     * Return current customer from regisrty
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return Mage::registry('checkout_current_customer');
    }

    /**
     * Return current store from regisrty
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        return Mage::registry('checkout_current_store');
    }

    /**
     * Returns javascript list type of this grid
     *
     * @return string
     */
    public function getListType()
    {
        return $this->_listType;
    }

    /**
     * Returns url to configure item
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        $params = array(
            'customer'   => $this->_getCustomer()->getId(),
            'store'    => $this->_getStore()->getId()
        );
        return $this->getUrl($this->_configureRoute, $params);
    }

    /**
     * Returns additional javascript to init this grid
     *
     * @return Mage_Core_Model_Store
     */
    public function getAdditionalJavaScript ()
    {
        return "Event.observe(window, 'load',  function() {\n"
            . "setTimeout(function(){productConfigure.addListType('" . $this->getListType() . "', {urlFetch: '" . $this->getConfigureUrl() . "'})\n"
            . "});\n"
            . "checkoutObj.addSourceGrid({htmlId: '" . $this->getId() . "', listType: '" . $this->getListType() . "'});\n}, 10)";
    }
}
