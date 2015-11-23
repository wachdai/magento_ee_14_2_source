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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Gift wrapping order create items info block
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Order_Create_Items
    extends Enterprise_GiftWrapping_Block_Adminhtml_Order_Create_Abstract
{
    /**
     * Select element for choosing gift wrapping design
     *
     * @return array
     */
    public function getDesignSelectHtml()
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setData(array(
                'id'    => 'giftwrapping_design_item',
                'class' => 'select'
            ))
            ->setOptions($this->getDesignCollection()->toOptionArray());
        return $select->getHtml();
    }

    /**
     * Prepare and return quote items info
     *
     * @return Varien_Object
     */
    public function getItemsInfo()
    {
        $data = array();
        foreach ($this->getQuote()->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($this->getDisplayGiftWrappingForItem($item)) {
                $temp = array();
                if ($price = $item->getProduct()->getGiftWrappingPrice()) {
                    if ($this->getDisplayWrappingBothPrices()) {
                        $temp['price_incl_tax'] = $this->calculatePrice(new Varien_Object(), $price, true);
                        $temp['price_excl_tax'] = $this->calculatePrice(new Varien_Object(), $price);
                    } else {
                        $temp['price'] = $this->calculatePrice(new Varien_Object(), $price,
                            $this->getDisplayWrappingPriceInclTax()
                        );
                    }
                }
                $temp['design'] = $item->getGwId();
                $data[$item->getId()] = $temp;
            }
        }
        return new Varien_Object($data);
    }

    /**
     * Check ability to display gift wrapping for items during backend order create
     *
     * @return bool
     */
    public function canDisplayGiftWrappingForItems()
    {
        $canDisplay = false;
        $count = count($this->getDesignCollection());
        if ($count) {
            foreach ($this->getQuote()->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($this->getDisplayGiftWrappingForItem($item)) {
                    $canDisplay = true;
                }
            }
        }
        return $canDisplay;
    }

    /**
     * Check ability to display gift wrapping for quote item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return bool
     */
    public function getDisplayGiftWrappingForItem($item)
    {
        $allowed = $item->getProduct()->getGiftWrappingAvailable();
        return Mage::helper('enterprise_giftwrapping')->isGiftWrappingAvailableForProduct($allowed, $this->getStoreId());
    }
}
