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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * RMA Grid
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('rmaGrid');
        $this->setDefaultSort('date_requested');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare related item collection
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _prepareCollection()
    {
        $this->_beforePrepareCollection();
        return parent::_prepareCollection();
    }

    /**
     * Configuring and setting collection
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _beforePrepareCollection()
    {
        if (!$this->getCollection()) {
            $collection = Mage::getResourceModel('enterprise_rma/rma_grid_collection');
            $this->setCollection($collection);
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('enterprise_rma')->__('RMA #'),
            'width'  => '50px',
            'type'   => 'text',
            'index'  => 'increment_id'
        ));

        $this->addColumn('date_requested', array(
            'header' => Mage::helper('enterprise_rma')->__('Date Requested'),
            'index' => 'date_requested',
            'type' => 'datetime',
            'html_decorators' => array('nobr'),
            'width' => 1,
        ));

        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('enterprise_rma')->__('Order #'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'order_increment_id'
        ));

        $this->addColumn('order_date', array(
            'header' => Mage::helper('enterprise_rma')->__('Order Date'),
            'index' => 'order_date',
            'type' => 'datetime',
            'html_decorators' => array('nobr'),
            'width' => 1,
        ));

        $this->addColumn('customer_name', array(
            'header' => Mage::helper('enterprise_rma')->__('Customer Name'),
            'index' => 'customer_name',
        ));

        $this->addColumn('status', array(
            'header'  => Mage::helper('enterprise_rma')->__('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'width'   => '100px',
            'options' => Mage::getModel('enterprise_rma/rma')->getAllStatuses()
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('enterprise_rma')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('enterprise_rma')->__('View'),
                        'url'       => array('base'=> $this->_getControllerUrl('edit')),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare massaction
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_ids');

        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('enterprise_rma')->__('Close'),
            'url'  => $this->getUrl($this->_getControllerUrl('close')),
            'confirm'  => Mage::helper('enterprise_rma')->__("You have chosen to change status(es) of the selected RMA requests to Close.\nAre you sure you want to proceed?")
        ));

        return $this;
    }

    /**
     * Get Url to action
     *
     * @param  string $action action Url part
     * @return string
     */
    protected function _getControllerUrl($action = '')
    {
        return '*/*/' . $action;
    }

    /**
     * Retrieve row url
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl($this->_getControllerUrl('edit'), array(
            'id' => $row->getId()
        ));
    }
}
