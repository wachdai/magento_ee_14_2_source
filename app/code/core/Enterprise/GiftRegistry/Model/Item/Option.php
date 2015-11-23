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
 * Gift registry item option model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftRegistry_Model_Item_Option extends Mage_Core_Model_Abstract
    implements Mage_Catalog_Model_Product_Configuration_Item_Option_Interface
{
    /**
     * Related gift registry item
     *
     * @var Enterprise_GiftRegistry_Model_Item
     */
    protected $_item;

    /**
     * Product related to option
     *
     * @var Mage_Catalog_Model_Product $product
     */
    protected $_product;

    /**
     * Internal constructor
     * Initializes resource model
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftregistry/item_option');
    }

    /**
     * Checks if item option model has data changes
     *
     * @return boolean
     */
    protected function _hasModelChanged()
    {
        if (!$this->hasDataChanges()) {
            return false;
        }

        return $this->_getResource()->hasDataChanged($this);
    }

    /**
     * Set related gift registry item
     *
     * @param   Enterprise_GiftRegistry_Model_Item $item
     * @return  Enterprise_GiftRegistry_Model_Item_Option
     */
    public function setItem($item)
    {
        $this->setItemId($item->getId());
        $this->_item = $item;
        return $this;
    }

    /**
     * Retrieve related gift registry item
     *
     * @return Enterprise_GiftRegistry_Model_Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Set product related to option
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Enterprise_GiftRegistry_Model_Item_Option
     */
    public function setProduct($product)
    {
        if (!empty($product) && !is_null($product->getId())) {
            $this->setProductId($product->getId());
            $this->_product = $product;
        }
        return $this;
    }

    /**
     * Retrieve product related to option
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Get option value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_getData('value');
    }

    /**
     * Initialize item identifier before data save
     *
     * @return Enterprise_GiftRegistry_Model_Item_Option
     */
    protected function _beforeSave()
    {
        if ($this->getItem()) {
            $this->setItemId($this->getItem()->getId());
        }
        return parent::_beforeSave();
    }

    /**
     * Clone option object
     *
     * @return Enterprise_GiftRegistry_Model_Item_Option
     */
    public function __clone()
    {
        $this->setId(null);
        $this->_item = null;
        return $this;
    }
}
