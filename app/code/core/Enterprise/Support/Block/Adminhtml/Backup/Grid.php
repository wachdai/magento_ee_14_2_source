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

class Enterprise_Support_Block_Adminhtml_Backup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('enterprise_support_backup');
        $this->setDefaultSort('backup_id');
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
        $collection = Mage::getModel('enterprise_support/backup')
            ->getCollection();
        $collection->removeBackupsWhereStatusFailed();
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
     * @return Enterprise_Support_Block_Adminhtml_Backup_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('backup_id', array(
            'header'    => Mage::helper('enterprise_support')->__('ID'),
            'index'     => 'backup_id',
            'type'      => 'text',
            'width'     => 20,
        ));

        $this->addColumn('code_dump', array(
            'header'    => Mage::helper('enterprise_support')->__('Code Dump'),
            'index'     => 'name',
            'type'      => 'text',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'Enterprise_Support_Block_Adminhtml_Backup_Grid_Column_Renderer_Code',
        ));

        $this->addColumn('db_dump', array(
            'header'    => Mage::helper('enterprise_support')->__('DB Dump'),
            'index'     => 'name',
            'type'      => 'text',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'Enterprise_Support_Block_Adminhtml_Backup_Grid_Column_Renderer_Db',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('enterprise_support')->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::helper('enterprise_support')->getStatusOptions(),
            'width'     => 300,
            'escape'    => true
        ));

        $this->addColumn('last_update', array(
            'header'    => Mage::helper('enterprise_support')->__('Last Update'),
            'index'     => 'last_update',
            'type'      => 'datetime',
            'escape'    => true,
            'width'     => 200,
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('enterprise_support')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('enterprise_support')->__('Show Log'),
                        'url'       => array('base'=> '*/*/log'),
                        'field'     => 'id'
                    ),
                    array(
                        'caption'   => Mage::helper('enterprise_support')->__('Delete'),
                        'url'       => array('base'=> '*/*/delete'),
                        'field'     => 'id',
                        'confirm'   => Mage::helper('enterprise_support')->__('Are you sure you want to delete the backup?'),
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return $this;
    }
}
