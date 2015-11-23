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
 * Accordion grid for products in wishlist
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Wishlist
    extends Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Abstract
{
    /**
     * Collection field name for using in controls
     * @var string
     */
    protected $_controlFieldName = 'wishlist_item_id';

    /**
     * Javascript list type name for this grid
     */
    protected $_listType = 'wishlist';

    /**
     * Url to configure this grid's items
     */
    protected $_configureRoute = '*/checkout/configureWishlistItem';

    /**
     * Initialize Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('source_wishlist');
        $this->setDefaultSort('added_at');
        $this->setData('open', true);
        $this->setHeaderText(
            Mage::helper('enterprise_checkout')->__('Wishlist (%s)', $this->getItemsCount())
        );
    }

    /**
     * Return custom object name for js grid
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return 'wishlistItemsGrid';
    }

    /**
     * Create wishlist item collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _createItemsCollection()
    {
        return Mage::getModel('wishlist/item')->getCollection();
    }

    /**
     * Return items collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function getItemsCollection()
    {
        if (!$this->hasData('items_collection')) {
            $collection = $this->_createItemsCollection()
                ->addCustomerIdFilter($this->_getCustomer()->getId())
                ->addStoreFilter($this->_getStore()->getWebsite()->getStoreIds())
                ->setVisibilityFilter()
                ->setSalableFilter()
                ->resetSortOrder();

            foreach ($collection as $item) {
                $product = $item->getProduct();
                if ($product) {
                    if (!$product->getStockItem()->getIsInStock() || !$product->isInStock()) {
                        // Remove disabled and out of stock products from the grid
                        $collection->removeItemByKey($item->getId());
                    } else {
                        $item->setName($product->getName());
                        $item->setPrice($product->getPrice());
                    }
                }

            }
            $this->setData('items_collection', $collection);
        }
        return $this->_getData('items_collection');
    }

    /**
     * Return grid URL for sorting and filtering
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/viewWishlist', array('_current'=>true));
    }

    /**
     * Add columns with controls to manage added products and their quantity
     * Uses inherited methods, but modifies Qty column to change renderer
     *
     * @return Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Wishlist
     */
    protected function _addControlColumns()
    {
        parent::_addControlColumns();

        $this->addColumn('qty', array(
            'sortable'  => false,
            'header'    => Mage::helper('enterprise_checkout')->__('Qty To Add'),
            'renderer'  => 'enterprise_checkout/adminhtml_manage_grid_renderer_wishlist_qty',
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
}
