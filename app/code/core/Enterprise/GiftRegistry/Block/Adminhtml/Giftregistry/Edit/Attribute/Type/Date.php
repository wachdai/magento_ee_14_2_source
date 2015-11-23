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

class Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Edit_Attribute_Type_Date
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/giftregistry/edit/type/date.phtml');
    }

    /**
     * Select element for choosing attribute type
     *
     * @return string
     */
    public function getDateFormatSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id'    =>  '{{prefix}}_attribute_{{id}}_date_format',
                'class' => 'select global-scope'
            ))
            ->setName('attributes[{{prefix}}][{{id}}][date_format]')
            ->setOptions($this->getDateFormatOptions());

        return $select->getHtml();
    }

    /**
     * Return array of date formats
     *
     * @return array
     */
    public function getDateFormatOptions()
    {
         return array(
            array(
                'value' => Mage_Core_Model_Locale::FORMAT_TYPE_SHORT,
                'label' => Mage::helper('enterprise_giftregistry')->__('Short')
            ),
            array(
                'value' => Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
                'label' => Mage::helper('enterprise_giftregistry')->__('Medium')
            ),
            array(
                'value' => Mage_Core_Model_Locale::FORMAT_TYPE_LONG,
                'label' => Mage::helper('enterprise_giftregistry')->__('Long')
            ),
            array(
                'value' => Mage_Core_Model_Locale::FORMAT_TYPE_FULL,
                'label' => Mage::helper('enterprise_giftregistry')->__('Full')
            )
        );
    }
}
