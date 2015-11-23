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
 * Add-by-SKU grid with errors
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid ID
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setId('sku_errors');
        $this->setRowClickCallback(null);
    }

    /**
     * Prepare collection of errors
     *
     * @return Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Grid
     */
    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();
        $removeButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button', '', array(
            'class' => 'delete',
            'label' => '',
            'onclick' => 'addBySku.removeFailedItem(this)',
            'type' => 'button',
        ))->toHtml();
        /* @var $parentBlock Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Abstract */
        $parentBlock = $this->getParentBlock();
        foreach ($parentBlock->getFailedItems() as $affectedItem) {
            // Escape user-submitted input
            if (isset($affectedItem['item']['qty'])) {
                $affectedItem['item']['qty'] = empty($affectedItem['item']['qty'])
                    ? ''
                    : (float)$affectedItem['item']['qty'];
            }
            $item = new Varien_Object();
            $item->setCode($affectedItem['code']);
            if (isset($affectedItem['error'])) {
                $item->setError($affectedItem['error']);
            }
            $item->addData($affectedItem['item']);
            $item->setId($item->getSku());
            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product');
            if (isset($affectedItem['item']['id'])) {
                $productId = $affectedItem['item']['id'];
                $item->setProductId($productId);
                $product->load($productId);
                /* @var $stockStatus Mage_CatalogInventory_Model_Stock_Status */
                $stockStatus = Mage::getModel('cataloginventory/stock_status');
                $status = $stockStatus->getProductStatus($productId, $this->getWebsiteId());
                if (!empty($status[$productId])) {
                    $product->setIsSalable($status[$productId]);
                }
                $item->setPrice(Mage::helper('core')->formatPrice($product->getPrice()));
            }
            $descriptionBlock = $this->getLayout()->createBlock(
                'enterprise_checkout/adminhtml_sku_errors_grid_description',
                '',
                array('product' => $product, 'item' => $item)
            );
            $item->setDescription($descriptionBlock->toHtml());
            $item->setRemoveButton($removeButtonHtml);
            $collection->addItem($item);
        }
        $this->setCollection($collection);
        return $this;
    }

    /**
     * Describe columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('description', array(
            'header'   => $this->__('Product Name'),
            'index'    => 'description',
            'class'    => 'no-link',
            'sortable' => false,
            'renderer' => 'enterprise_checkout/adminhtml_sku_errors_grid_renderer_html',
        ));

        $this->addColumn('price', array(
            'header'   => $this->__('Price'),
            'class'    => 'no-link',
            'width'    => 100,
            'index'    => 'price',
            'sortable' => false,
            'type'     => 'text',
        ));

        $this->addColumn('qty', array(
            'header'   => $this->__('Qty'),
            'class'    => 'no-link sku-error-qty',
            'width'    => 40,
            'sortable' => false,
            'index'    => 'qty',
            'renderer' => 'enterprise_checkout/adminhtml_sku_errors_grid_renderer_qty',
        ));

        $this->addColumn('remove', array(
            'header'   => $this->__('Remove'),
            'class'    => 'no-link',
            'width'    => 80,
            'index'    => 'remove_button',
            'sortable' => false,
            'renderer' => 'enterprise_checkout/adminhtml_sku_errors_grid_renderer_html',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Disable unnecessary functionality
     *
     * @return Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Grid
     */
    public function _prepareLayout()
    {
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        return $this;
    }

    /**
     * Retrieve row css class for specified item
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getRowClass(Varien_Object $item)
    {
        if ($item->getCode() == Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED) {
            return 'qty-not-available';
        }
        return '';
    }

    /**
     * Get current website ID
     *
     * @return int|null|string
     */
    public function getWebsiteId()
    {
        return $this->getParentBlock()->getStore()->getWebsiteId();
    }

    /**
     * Retrieve empty row urls for the grid
     *
     * @param Mage_Catalog_Model_Product|Varien_Object $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return '';
    }
}
