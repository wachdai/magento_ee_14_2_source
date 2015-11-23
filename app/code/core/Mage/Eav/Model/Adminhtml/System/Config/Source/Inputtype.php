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
 * @category    Mage
 * @package     Mage_Eav
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'text', 'label' => Mage::helper('eav')->__('Text Field')),
            array('value' => 'textarea', 'label' => Mage::helper('eav')->__('Text Area')),
            array('value' => 'date', 'label' => Mage::helper('eav')->__('Date')),
            array('value' => 'boolean', 'label' => Mage::helper('eav')->__('Yes/No')),
            array('value' => 'multiselect', 'label' => Mage::helper('eav')->__('Multiple Select')),
            array('value' => 'select', 'label' => Mage::helper('eav')->__('Dropdown'))
        );
    }
}
