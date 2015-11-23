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
 * Invitations grid
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Adminhtml_Invitation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set defaults
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('invitationGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Invitation_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('enterprise_invitation/invitation')->getCollection()
            ->addWebsiteInformation()->addInviteeInformation();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Invitation_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('enterprise_invitation_id', array(
            'header'=> Mage::helper('enterprise_invitation')->__('ID'),
            'width' => 80,
            'align' => 'right',
            'type'  => 'text',
            'index' => 'invitation_id'
        ));

        $this->addColumn('email', array(
            'header' => Mage::helper('enterprise_invitation')->__('Email'),
            'index' => 'invitation_email',
            'type'  => 'text'
        ));

        $renderer = (Mage::getSingleton('admin/session')->isAllowed('customer/manage'))
            ? 'enterprise_invitation/adminhtml_invitation_grid_column_invitee' : false;

        $this->addColumn('invitee', array(
            'header' => Mage::helper('enterprise_invitation')->__('Invitee'),
            'index'  => 'invitee_email',
            'type'   => 'text',
            'renderer' => $renderer,
        ));

        $this->addColumn('invitation_date', array(
            'header' => Mage::helper('enterprise_invitation')->__('Sent'),
            'index' => 'invitation_date',
            'type' => 'datetime',
            'gmtoffset' => true,
            'width' => 170
        ));

        $this->addColumn('signup_date', array(
            'header' => Mage::helper('enterprise_invitation')->__('Registered'),
            'index' => 'signup_date',
            'type' => 'datetime',
            'gmtoffset' => true,
            'width' => 150
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('enterprise_invitation')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('enterprise_invitation/source_invitation_status')->getOptions(),
            'width' => 140
        ));

        $this->addColumn('website_id', array(
            'header'  => Mage::helper('enterprise_invitation')->__('Valid on Website'),
            'index'   => 'website_id',
            'type'    => 'options',
            'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
            'width'   => 150,
        ));

        $groups = Mage::getModel('customer/group')->getCollection()
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group_id', array(
            'header' => Mage::helper('enterprise_invitation')->__('Invitee Group'),
            'index' => 'group_id',
            'filter_index' => 'invitee_group_id',
            'type'  => 'options',
            'options' => $groups,
            'width' => 140
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass-actions
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Invitation_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('invitation_id');
        $this->getMassactionBlock()->setFormFieldName('invitations');
        $this->getMassactionBlock()->addItem('cancel', array(
                'label' => $this->helper('enterprise_invitation')->__('Discard Selected'),
                'url' => $this->getUrl('*/*/massCancel'),
                'confirm' => Mage::helper('enterprise_invitation')->__('Are you sure you want to do this?')
        ));

        $this->getMassactionBlock()->addItem('resend', array(
                'label' => $this->helper('enterprise_invitation')->__('Send Selected'),
                'url' => $this->getUrl('*/*/massResend')
        ));

        return parent::_prepareMassaction();
    }

    /**
     * Row clock callback
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
}
