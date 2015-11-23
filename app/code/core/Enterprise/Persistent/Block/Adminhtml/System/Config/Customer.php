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
 * @package     Enterprise_Persistent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise Persistent System Config Option Customer Segmentation admin frontend model
 *
 */
class Enterprise_Persistent_Block_Adminhtml_System_Config_Customer extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $elementId = $element->getHtmlId();
        $optionShoppingCartId = str_replace('/', '_', Mage_Persistent_Helper_Data::XML_PATH_PERSIST_SHOPPING_CART);
        $optionEnabled = str_replace('/', '_', Mage_Persistent_Helper_Data::XML_PATH_ENABLED);

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
        }

        $html = '<script type="text/javascript">
            PersistentCustomerSegmentation = Class.create();
            PersistentCustomerSegmentation.prototype = {
                initialize : function () {
                    this._element = $("'.$elementId.'");
                    var funcTrackOnChangeShoppingCart = this.trackOnChangeShoppingCart.bind(this);
                    document.observe("dom:loaded", funcTrackOnChangeShoppingCart);
                    $("'.$optionShoppingCartId.'").observe("change", funcTrackOnChangeShoppingCart);
                    $("'.$optionEnabled.'").observe("change", function() {
                        setTimeout(funcTrackOnChangeShoppingCart, 1);
                    });'
                    .(($addInheritCheckbox)?
                        '$("'.$elementId.'_inherit").observe("change", funcTrackOnChangeShoppingCart);' : '')
                .'},

                disable: function() {
                    this._element.disabled = true;
                    this._element.value = 1;
                },

                enable: function() {
                    this._element.disabled = false;
                },

                trackOnChangeShoppingCart: function() {
                    if ($("'.$optionEnabled.'").value == 1 && $("'.$optionShoppingCartId.'").value == 1 ) {
                         this.disable();
                    } else {
                        '.(($addInheritCheckbox)? 'if ($("'.$elementId.'_inherit").checked) {
                            this.disable();
                        } else {
                            this.enable();
                        }' : 'this.enable();' ).'

                    }
                }
            };
        var persistentCustomerSegmentation = new PersistentCustomerSegmentation();
        </script>';

        return parent::render($element).$html;
    }
}
