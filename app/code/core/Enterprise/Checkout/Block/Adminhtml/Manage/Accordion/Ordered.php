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
 * Accordion grid for recently ordered products
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Ordered
    extends Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Abstract
{
    /**
     * Collection field name for using in controls
     * @var string
     */
    protected $_controlFieldName = 'item_id';

    /**
     * Javascript list type name for this grid
     */
    protected $_listType = 'ordered';

    /**
     * Url to configure this grid's items
     */
    protected $_configureRoute = '*/checkout/configureOrderedItem';

    /**
     * Initialize Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('source_ordered');
        $this->setHeaderText(
            Mage::helper('enterprise_checkout')->__('Last ordered items (%s)', $this->getItemsCount())
        );
    }

    /**
     * Returns custom last ordered products renderer for price column content
     *
     * @return null|string
     */
    protected function _getPriceRenderer()
    {
        return 'enterprise_checkout/adminhtml_manage_grid_renderer_ordered_price';
    }

    /**
     * Prepare customer wishlist product collection
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function getItemsCollection()
    {
        if (!$this->hasData('items_collection')) {
            $productIds = array();
            $storeIds = $this->_getStore()->getWebsite()->getStoreIds();

            // Load last order of a customer
            /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToFilter('customer_id', $this->_getCustomer()->getId())
                ->addAttributeToFilter('store_id', array('in' => $storeIds))
                ->addAttributeToSort('created_at', 'desc')
                ->setPage(1, 1)
                ->load();
            foreach ($collection as $order) {
                break;
            }

            // Add products to order items
            if (isset($order)) {
                $productIds = array();
                $collection = $order->getItemsCollection();
                foreach ($collection as $item) {
                    if ($item->getParentItem()) {
                        $collection->removeItemByKey($item->getId());
                    } else {
                        $productIds[$item->getProductId()] = $item->getProductId();
                    }
                }
                if ($productIds) {
                    // Load products collection
                    $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
                    $products = Mage::getModel('catalog/product')->getCollection()
                        ->setStore($this->_getStore())
                        ->addAttributeToSelect($attributes)
                        ->addAttributeToSelect('sku')
                        ->addAttributeToFilter('type_id',
                            array_keys(
                                Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')
                                    ->asArray()
                            )
                        )->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        ->addStoreFilter($this->_getStore())
                        ->addIdFilter($productIds);
                    Mage::getSingleton('cataloginventory/stock_status')->addIsInStockFilterToCollection($products);
                    Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($products);
                    $products->addOptionsToResult();

                    // Set products to items
                    foreach ($collection as $item) {
                        $productId = $item->getProductId();
                        $product = $products->getItemById($productId);
                        if ($product) {
                            $item->setProduct($product);
                        } else {
                            $collection->removeItemByKey($item->getId());
                        }
                    }
                }
            }
            $this->setData('items_collection', $productIds ? $collection : parent::getItemsCollection());
        }
        return $this->getData('items_collection');
    }

    /**
     * Return grid URL for sorting and filtering
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/viewOrdered', array('_current'=>true));
    }
}
