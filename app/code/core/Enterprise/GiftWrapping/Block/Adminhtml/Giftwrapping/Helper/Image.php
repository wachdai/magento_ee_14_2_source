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
 * Gift Wrapping Image Helper
 *
 * @category   Enterprise
 * @package    Enterprise_GiftWrapping
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Giftwrapping_Helper_Image extends Varien_Data_Form_Element_Image
{
    /**
     * Get gift wrapping image url
     *
     * @return string|boolean
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->getForm()->getDataObject()->getImageUrl();
        }
        return $url;
    }

    /**
     * Get default field name
     *
     * @return string
     */
    public function getDefaultName()
    {
        $name = $this->getData('name');
        $suffix = $this->getForm()->getFieldNameSuffix();
        if ($suffix) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

}
