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
 * @package     Enterprise_Customer
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Form Types Grid Block
 *
 * @category   Enterprise
 * @package    Enterprise_Customer
 */
class Enterprise_Customer_Block_Adminhtml_Customer_Formtype_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize Grid Block
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('code');
        $this->setDefaultDir('asc');
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprice_Customer_Block_Adminhtml_Customer_Formtype_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('eav/form_type')
            ->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Enterprice_Customer_Block_Adminhtml_Customer_Formtype_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header'    => Mage::helper('enterprise_customer')->__('Form Type Code'),
            'index'     => 'code',
        ));

        $this->addColumn('label', array(
            'header'    => Mage::helper('enterprise_customer')->__('Label'),
            'index'     => 'label',
        ));

        $this->addColumn('store_id', array(
            'header'    => Mage::helper('enterprise_customer')->__('Store View'),
            'index'     => 'store_id',
            'type'      => 'store'
        ));

        $design = Mage::getModel('core/design_source_design')
            ->setIsFullLabel(true)->getAllOptions(false);
        array_unshift($design, array(
            'value' => 'all',
            'label' => Mage::helper('enterprise_customer')->__('All Themes')
        ));
        $this->addColumn('theme', array(
            'header'     => Mage::helper('enterprise_customer')->__('For Theme'),
            'type'       => 'theme',
            'index'      => 'theme',
            'options'    => $design,
            'with_empty' => true,
            'default'    => Mage::helper('enterprise_customer')->__('All Themes')
        ));

        $this->addColumn('is_system', array(
            'header'    => Mage::helper('enterprise_customer')->__('System'),
            'index'     => 'is_system',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('enterprise_customer')->__('No'),
                1 => Mage::helper('enterprise_customer')->__('Yes'),
            )
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve row click URL
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('type_id' => $row->getId()));
    }
}
