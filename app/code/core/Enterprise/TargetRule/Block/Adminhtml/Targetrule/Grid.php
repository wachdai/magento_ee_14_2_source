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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Admin Targer Rules Grid
 */
class Enterprise_TargetRule_Block_Adminhtml_Targetrule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('TargetRuleGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprise_TargetRule_Block_Adminhtml_Targetrule_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Enterprise_TargetRule_Model_Mysql4_Rule_Collection */
        $collection = Mage::getModel('enterprise_targetrule/rule')
            ->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Get grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Retrieve URL for Row click
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id'    => $row->getId()
        ));
    }

    /**
     * Define grid columns
     *
     * @return Enterprise_TargetRule_Block_Adminhtml_Targetrule_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('ID'),
            'index'     => 'rule_id',
            'type'      => 'text',
            'width'     => 20,
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('Rule Name'),
            'index'     => 'name',
            'type'      => 'text',
            'escape'    => true
        ));

        $this->addColumn('from_date', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('Date Start'),
            'index'     => 'from_date',
            'type'      => 'date',
            'default'   => '--',
            'width'     => 160,
        ));

        $this->addColumn('to_date', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('Date Expire'),
            'index'     => 'to_date',
            'type'      => 'date',
            'default'   => '--',
            'width'     => 160,
        ));

        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('Priority'),
            'index'     => 'sort_order',
            'type'      => 'text',
            'width'     => 1,
        ));

        $this->addColumn('apply_to', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('Applies To'),
            'align'     => 'left',
            'index'     => 'apply_to',
            'type'      => 'options',
            'options'   => Mage::getSingleton('enterprise_targetrule/rule')->getAppliesToOptions(),
            'width'     => 150,
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('enterprise_targetrule')->__('Status'),
            'align'     => 'left',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => Mage::helper('enterprise_targetrule')->__('Active'),
                0 => Mage::helper('enterprise_targetrule')->__('Inactive'),
            ),
            'width'     => 1,
        ));

        return $this;
    }
}
