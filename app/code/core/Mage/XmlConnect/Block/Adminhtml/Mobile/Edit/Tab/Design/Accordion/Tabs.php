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
 * @package     Mage_XmlConnect
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Device tabs accordion block
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_Design_Accordion_Tabs
    extends Mage_XmlConnect_Block_Adminhtml_Mobile_Widget_Form
{
    /**
     * Getter for accordion item title
     *
     * @return string
     */
    public function getTitle()
    {
        if (Mage::helper('xmlconnect')->getDeviceType() == Mage_XmlConnect_Helper_Data::DEVICE_TYPE_IPAD) {
            $title = $this->__('Extensions');
        } else {
            $title = $this->__('Tabs');
        }
        return $title;
    }

    /**
     * Getter for accordion item is open flag
     *
     * @return bool
     */
    public function getIsOpen()
    {
        return true;
    }

    /**
     * Prepare form
     *
     * @return Mage_XmlConnect_Block_Adminhtml_Mobile_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('field_tabs', array());
        $this->_addElementTypes($fieldset);
        $fieldset->addField('conf[extra][tabs]', 'tabs', array('name' => 'conf[extra][tabs]'));

        $form->setValues(Mage::helper('xmlconnect')->getApplication()->getFormData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
