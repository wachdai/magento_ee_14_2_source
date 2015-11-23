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

class Enterprise_GiftRegistry_Block_Cart_Product_Mark extends Mage_Core_Block_Template
{
    /**
     * Check whether module is available
     *
     * @return bool
     */
    public function getEnabled()
    {
        return  Mage::helper('enterprise_giftregistry')->isEnabled();
    }

    /**
     * Get current quote item from parent block
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setData('item', null);
        $item = $this->getLayout()->getBlock('additional.product.info')->getItem();

        if ($item instanceof  Mage_Sales_Model_Quote_Address_Item) {
            $item = $item->getQuoteItem();
        }

        if (!$item || !$item->getGiftregistryItemId()) {
            return '';
        }

        $this->setItem($item);

        if (!$this->getEntity() || !$this->getEntity()->getId()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get gifregistry params by quote item
     *
     * @param Mage_Sales_Model_Quote_Item $newItem
     * @return Enterprise_GiftRegistry_Block_Cart_Product_Mark
     */
    public function setItem($newItem)
    {
        if ($this->hasItem() && $this->getItem()->getId() == $newItem->getId()) {
            return $this;
        }

        if ($newItem->getGiftregistryItemId()) {
            $this->setData('item', $newItem);
            $entity = Mage::getModel('enterprise_giftregistry/entity')->loadByEntityItem($newItem->getGiftregistryItemId());
            $this->setEntity($entity);
        }

        return $this;
    }

    /**
     * Return current giftregistry title
     *
     * @return string
     */
    public function getGiftregistryTitle()
    {
        return $this->escapeHtml($this->getEntity()->getTitle());
    }

    /**
     * Return current giftregistry view url
     *
     * @return string
     */
    public function getGiftregistryUrl()
    {
        return $this->getUrl('enterprise_giftregistry/view/index', array('id' => $this->getEntity()->getUrlKey()));
    }
}
