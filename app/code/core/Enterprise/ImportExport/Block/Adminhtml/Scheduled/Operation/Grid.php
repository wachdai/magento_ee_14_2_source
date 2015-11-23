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
 * @package     Enterprise_ImportExport
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Scheduled operation grid
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setId('operationGrid');
        $this->_controller = 'adminhtml_scheduled_operation';
        $this->setUseAjax(true);

        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('enterprise_importexport/scheduled_operation_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Grid columns definition
     *
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'        => Mage::helper('enterprise_importexport')->__('Name'),
            'index'         => 'name',
            'type'          => 'text',
            'escape'        => true
        ));

        $dataModel = Mage::getSingleton('enterprise_importexport/scheduled_operation_data');
        $this->addColumn('operation_type', array(
            'header'        => Mage::helper('enterprise_importexport')->__('Operation'),
            'width'         => '30px',
            'index'         => 'operation_type',
            'type'          => 'options',
            'options'       => $dataModel->getOperationsOptionArray()
        ));

        $this->addColumn('entity_type', array(
            'header'        => Mage::helper('enterprise_importexport')->__('Entity type'),
            'index'         => 'entity_type',
            'type'          => 'options',
            'options'       => $dataModel->getEntitiesOptionArray()
        ));

        $this->addColumn('last_run_date', array(
            'header'        => Mage::helper('enterprise_importexport')->__('Last Run Date'),
            'index'         => 'last_run_date',
            'type'          => 'datetime'
        ));

        $this->addColumn('freq', array(
            'header'        => Mage::helper('enterprise_importexport')->__('Frequency'),
            'index'         => 'freq',
            'type'          => 'options',
            'options'       => $dataModel->getFrequencyOptionArray(),
            'width'         => '100px'
        ));

        $this->addColumn('status', array(
            'header'        => Mage::helper('enterprise_importexport')->__('Status'),
            'index'         => 'status',
            'type'          => 'options',
            'options'       => $dataModel->getStatusesOptionArray()
        ));

        $this->addColumn('is_success', array(
            'header'        => Mage::helper('enterprise_importexport')->__('Last Outcome'),
            'index'         => 'is_success',
            'type'          => 'options',
            'width'         => '200px',
            'options'       => $dataModel->getResultOptionArray()
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('enterprise_importexport')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('enterprise_importexport')->__('Edit'),
                    'url'     => array(
                        'base'=>'*/*/edit',
                    ),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('enterprise_importexport')->__('Run'),
                    'url'     => array(
                        'base'=> '*/scheduled_operation/cron',
                    ),
                    'field'   => 'operation'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'id',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get row url
     *
     * @param Enterprise_ImportExport_Model_Scheduled_Operation
     * @return string
     */
    public function getRowUrl($operation)
    {
        return $this->getUrl('*/*/edit', array(
            'id' => $operation->getId(),
        ));
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * Prepare batch actions
     *
     * @return Enterprise_ImportExport_Block_Adminhtml_Scheduled_Operation_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('operation');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('enterprise_importexport')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('enterprise_importexport')->__('Are you sure you want to delete the selected scheduled imports/exports?')
        ));

        $statuses = Mage::getSingleton('enterprise_importexport/scheduled_operation_data')
            ->getStatusesOptionArray();
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('enterprise_importexport')->__('Change status'),
            'url'  => $this->getUrl('*/*/massChangeStatus', array('_current' => true)),
            'additional' => array(
               'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('enterprise_importexport')->__('Status'),
                    'values' => $statuses
                )
             )
        ));

        return $this;
    }
}
