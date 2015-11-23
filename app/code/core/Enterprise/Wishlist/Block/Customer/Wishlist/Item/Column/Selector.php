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
 * @package     Enterprise_Wishlist
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Wishlist item selector in wishlist table
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Block_Customer_Wishlist_Item_Column_Selector
    extends Mage_Wishlist_Block_Customer_Wishlist_Item_Column
{
    /**
     * Render block
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::helper('enterprise_wishlist')->isMultipleEnabled() || $this->getIsEnabled();
    }

    /**
     * Retrieve column title
     *
     * @return string
     */
    public function getTitle()
    {
        return '<input type="checkbox" id="select-all" />';
    }

    /**
     * Get block javascript
     *
     * @return string
     */
    public function getJs()
    {
        return parent::getJs() . "
            var selector = $('select-all'),
                checkboxes = $(selector).up('#wishlist-table').select('.select'),
                counter = 0;
            if (!checkboxes.length) {
                selector.hide();
            }
            selector.setCounter = function (newVal) {
                counter = newVal;
                this.checked = (counter >= checkboxes.length);
            }
            selector.onclick = function(){
                checkboxes.each( (function(checkbox) {
                    checkbox.checked = this.checked;
                }).bind(this));
                counter = this.checked ? checkboxes.length : 0
            };
            checkboxes.each( function(checkbox) {
                checkbox.onclick = function() {
                    selector.setCounter(this.checked ? counter + 1: counter -1);
                }
            });
        ";
    }
}
