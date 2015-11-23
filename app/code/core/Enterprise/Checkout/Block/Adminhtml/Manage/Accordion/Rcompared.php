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
 * Accordion grid for Recently compared products
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Rcompared
    extends Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Abstract
{
    /**
     * Javascript list type name for this grid
     */
    protected $_listType = 'rcompared';

    /**
     * Initialize Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('source_rcompared');
        $this->setHeaderText(
            Mage::helper('enterprise_checkout')->__('Recently Compared Products (%s)', $this->getItemsCount())
        );

    }

    /**
     * Return items collection
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function getItemsCollection()
    {
        if (!$this->hasData('items_collection')) {
            $skipProducts = array();
            $collection = Mage::getModel('catalog/product_compare_list')
                ->getItemCollection()
                ->useProductItem(true)
                ->setStoreId($this->_getStore()->getId())
                ->addStoreFilter($this->_getStore()->getId())
                ->setCustomerId($this->_getCustomer()->getId());
            foreach ($collection as $_item) {
                $skipProducts[] = $_item->getProductId();
            }

            // prepare products collection and apply visitors log to it
            $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
            if (!in_array('status', $attributes)) {
                // Status attribute is required even if it is not used in product listings
                array_push($attributes, 'status');
            }
            $productCollection = Mage::getModel('catalog/product')->getCollection()
                ->setStoreId($this->_getStore()->getId())
                ->addStoreFilter($this->_getStore()->getId())
                ->addAttributeToSelect($attributes);
            Mage::getResourceSingleton('reports/event')->applyLogToCollection(
                $productCollection,
                Mage_Reports_Model_Event::EVENT_PRODUCT_COMPARE,
                $this->_getCustomer()->getId(),
                0,
                $skipProducts
            );
            $productCollection = Mage::helper('adminhtml/sales')->applySalableProductTypesFilter($productCollection);
            // Remove disabled and out of stock products from the grid
            foreach ($productCollection as $product) {
                if (!$product->getStockItem()->getIsInStock() || !$product->isInStock()) {
                    $productCollection->removeItemByKey($product->getId());
                }
            }
            $productCollection->addOptionsToResult();
            $this->setData('items_collection', $productCollection);
        }
        return $this->_getData('items_collection');
    }

    /**
     * Retrieve Grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/viewRecentlyCompared', array('_current'=>true));
    }
}
