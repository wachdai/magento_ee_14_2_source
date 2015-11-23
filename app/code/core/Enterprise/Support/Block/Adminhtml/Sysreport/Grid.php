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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Support_Block_Adminhtml_Sysreport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set Grid ID
     * Set grid sorting parameters
     *
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setId('enterprise_support_report');
        $this->setDefaultSort('report_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprise_Support_Block_Adminhtml_Backup_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('enterprise_support/sysreport')
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
     * Define grid columns
     *
     * @return Enterprise_Support_Block_Adminhtml_Sysreport_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('report_id', array(
            'header'    => Mage::helper('enterprise_support')->__('ID'),
            'index'     => 'report_id',
            'type'      => 'number',
            'width'     => '65px',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('enterprise_support')->__('Date Generated'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => '350px',
            'renderer'  => 'Enterprise_Support_Block_Adminhtml_Sysreport_Grid_Column_Renderer_Date',
        ));

        $this->addColumn('report_types', array(
            'header'    => Mage::helper('enterprise_support')->__('Report Types'),
            'index'     => 'report_types',
            'type'      => 'text',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'Enterprise_Support_Block_Adminhtml_Sysreport_Grid_Column_Renderer_Types',
        ));

        $this->addColumn('report_version', array(
            'header'    => Mage::helper('enterprise_support')->__('Report Version'),
            'index'     => 'report_version',
            'type'      => 'text',
            'escape'    => true,
            'width'     => '50px',
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('enterprise_support')->__('Action'),
                'width'     => '150px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('enterprise_support')->__('View'),
                        'url'       => array('base'=> '*/*/view'),
                        'field'     => 'id'
                    ),
                    array(
                        'caption'   => Mage::helper('enterprise_support')->__('Download'),
                        'url'       => array('base'=> '*/*/download'),
                        'field'     => 'id'
                    ),
                    array(
                        'caption'   => Mage::helper('enterprise_support')->__('Delete'),
                        'url'       => array('base'=> '*/*/delete'),
                        'field'     => 'id',
                        'confirm'   =>
                            Mage::helper('enterprise_support')->__('Are you sure you want to delete the system report?'),
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                'renderer'  => 'Enterprise_Support_Block_Adminhtml_Sysreport_Grid_Column_Renderer_Action',
            ));

        return $this;
    }

    /**
     * Prepare mass action options for this grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('report_id');
        $this->getMassactionBlock()->setFormFieldName('reports');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('enterprise_support')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('enterprise_support')->__('Are you sure you want to delete these system reports?')
        ));

        return $this;
    }

    /**
     * Row click url
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
}
