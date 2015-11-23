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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Adminhtml invitation general report grid block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Adminhtml_Report_Invitation_General_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    /**
     * Prepare report collection
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Report_Invitation_General_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('enterprise_invitation/report_invitation_collection');
        return $this;
    }

    /**
     * Prepare report grid columns
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Report_Invitation_General_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sent', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Sent'),
            'type'      =>'number',
            'index'     => 'sent'
        ));

        $this->addColumn('accepted', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Accepted'),
            'type'      =>'number',
            'index'     => 'accepted',
            'width'     => ''
        ));

        $this->addColumn('canceled', array(
            'header'    => Mage::helper('enterprise_invitation')->__('Discarded'),
            'type'      =>'number',
            'index'     => 'canceled',
            'width'     => ''
        ));

        $this->addColumn('accepted_rate', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Acceptance Rate'),
            'index'     =>'accepted_rate',
            'renderer'  => 'enterprise_invitation/adminhtml_grid_column_renderer_percent',
            'type'      =>'string',
            'width'     => '170'

        ));

        $this->addColumn('canceled_rate', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Discard Rate'),
            'index'     =>'canceled_rate',
            'type'      =>'number',
            'renderer'  => 'enterprise_invitation/adminhtml_grid_column_renderer_percent',
            'width'     => '170'
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('enterprise_invitation')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('enterprise_invitation')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
