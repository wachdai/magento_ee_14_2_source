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
 * @package     Enterprise_Index
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Index adminhtml process grid block.
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Block_Adminhtml_Process_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Process model
     *
     * @var Mage_Index_Model_Process
     */
    protected $_processModel;

    /**
     * Mass-action block
     *
     * @var string
     */
    protected $_massactionBlockName = 'enterprise_index/adminhtml_process_grid_massaction';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_processModel = Mage::getSingleton('enterprise_index/process');
        $this->setId('indexer_processes_grid');
        $this->_filterVisibility = false;
        $this->_pagerVisibility  = false;
    }

    /**
     * Prepare grid collection
     *
     * @return Mage_Index_Block_Adminhtml_Process_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection  Enterprise_Index_Model_Resource_Process_Collection */
        $collection = Mage::getResourceModel('enterprise_index/process_collection');
        Mage::dispatchEvent('enterprise_index_exclude_process_before', array('collection' => $collection));
        $collection->initializeSelect();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Add name and description to collection elements
     *
     * @return Mage_Index_Block_Adminhtml_Process_Grid
     */
    protected function _afterLoadCollection()
    {
        /** @var $item Mage_Index_Model_Process */
        foreach ($this->_collection as $key => $item) {
            if (!$item->getIndexer()->isVisible()) {
                $this->_collection->removeItemByKey($key);
                continue;
            }
            $item->setName($item->getIndexer()->getName());
            $item->setDescription($item->getIndexer()->getDescription());

            if (!$item->isEnterpriseProcess()) {
                $item->setUpdateRequired($item->getUnprocessedEventsCollection()->count() > 0 ? 1 : 0);
                if ($item->isLocked()) {
                    $item->setStatus(Mage_Index_Model_Process::STATUS_RUNNING);
                }
            } else {
                // For enterprise processes set update_required = -1 to display empty cell in grid.
                $item->setUpdateRequired(-1);
            }
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Index_Block_Adminhtml_Process_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('indexer_code', array(
            'header'    => Mage::helper('enterprise_index')->__('Index'),
            'width'     => '190',
            'align'     => 'left',
            'index'     => 'name',
            'sortable'  => false,
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('enterprise_index')->__('Description'),
            'align'     => 'left',
            'index'     => 'description',
            'sortable'  => false,
        ));

        $this->addColumn('mode', array(
            'header'    => Mage::helper('enterprise_index')->__('Mode'),
            'width'     => '150',
            'align'     => 'left',
            'index'     => 'mode',
            'type'      => 'options',
            'options'   => $this->_processModel->getModesOptions()
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('enterprise_index')->__('Status'),
            'width'     => '120',
            'align'     => 'left',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => $this->_processModel->getStatusesOptions(),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        $this->addColumn('update_required', array(
            'header'    => Mage::helper('enterprise_index')->__('Update Required'),
            'sortable'  => false,
            'width'     => '120',
            'align'     => 'left',
            'index'     => 'update_required',
            'type'      => 'options',
            'options'   => $this->_processModel->getUpdateRequiredOptions(),
            'frame_callback' => array($this, 'decorateUpdateRequired')
        ));

        $this->addColumn('ended_at', array(
            'header'    => Mage::helper('enterprise_index')->__('Updated At'),
            'type'      => 'datetime',
            'width'     => '180',
            'align'     => 'left',
            'index'     => 'ended_at'
        ));

        $this->addColumn('action',
            array(
                'renderer'  => 'enterprise_index/adminhtml_widget_grid_column_renderer_action',
                'header'    =>  Mage::helper('enterprise_index')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('enterprise_index')->__('Reindex Data'),
                        'url'       => array('base'=> '*/*/reindexProcess'),
                        'field'     => 'process'
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            ));

        parent::_prepareColumns();

        return $this;
    }

    /**
     * Decorate status column values
     *
     * @param string $value
     * @param Mage_Index_Model_Process $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     *
     * @return string
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getStatus()) {
            case Enterprise_Index_Model_Process::STATUS_PENDING :
                $class = 'grid-severity-notice';
                break;
            case Enterprise_Index_Model_Process::STATUS_RUNNING :
                $class = 'grid-severity-major';
                break;
            case Enterprise_Index_Model_Process::STATUS_REQUIRE_REINDEX :
                $class = 'grid-severity-critical';
                break;
            case Enterprise_Index_Model_Process::STATUS_SCHEDULED :
                $class = 'grid-severity-minor';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    /**
     * Decorate "Update Required" column values
     *
     * @param string $value
     * @param Mage_Index_Model_Process $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     *
     * @return string
     */
    public function decorateUpdateRequired($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getUpdateRequired()) {
            case 0:
                $class = 'grid-severity-notice';
                break;
            case 1:
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    /**
     * Decorate last run date column
     *
     * @param string $value
     * @param Mage_Index_Model_Process $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     *
     * @return string
     */
    public function decorateDate($value, $row, $column, $isExport)
    {
        if (!$value) {
            return $this->__('Never');
        }
        return $value;
    }

    /**
     * Get row edit url
     *
     * @param Mage_Index_Model_Process $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        if (!$row->isEnterpriseProcess()) {
            return $this->getUrl('*/*/edit', array('process' => $row->getId()));
        }
        return '';
    }

    /**
     * Add mass-actions to grid
     *
     * @return Mage_Index_Block_Adminhtml_Process_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('process_id');
        $this->getMassactionBlock()->setFormFieldName('process');

        $this->getMassactionBlock()->addItem('change_mode', array(
            'label'         => Mage::helper('enterprise_index')->__('Change Index Mode'),
            'url'           => $this->getUrl('*/*/massChangeMode'),
            'additional'    => array(
                'mode'      => array(
                    'name'      => 'index_mode',
                    'type'      => 'select',
                    'class'     => 'required-entry',
                    'label'     => Mage::helper('enterprise_index')->__('Index mode'),
                    'values'    => $this->_processModel->getModesOptions()
                )
            )
        ));

        $this->getMassactionBlock()->addItem('reindex', array(
            'label'    => Mage::helper('enterprise_index')->__('Reindex Data'),
            'url'      => $this->getUrl('*/*/massReindex'),
            'selected' => true,
        ));

        return $this;
    }

    /**
     * Prepare grid massaction column
     *
     * @return Enterprise_Index_Block_Adminhtml_Process_Grid|unknown
     */
    protected function _prepareMassactionColumn()
    {
        parent::_prepareMassactionColumn();

        $this->getColumn('massaction')
            ->setData('renderer', 'enterprise_index/adminhtml_widget_grid_column_renderer_massaction');

        return $this;
    }
}
