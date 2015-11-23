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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Staging Backup Grid
 *
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Backup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('enterpriseStagingBackupGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');

        $this->setUseAjax(true);
        $this->setMassactionBlock("vailable");

        $this->setColumnRenderers(
            array(
                'action' => 'enterprise_staging/adminhtml_widget_grid_column_renderer_action'
        ));
    }

    /**
     * Configuration of grid
     *
     * @return Enterprise_Staging_Block_Manage_Staging_Backup_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('enterprise_staging')->__('Website'),
            'index'     => 'name',
            'type'      => 'text',
            'sortable'  => false
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('enterprise_staging')->__('Created At'),
            'index'     => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('enterprise_staging')->__('Action'),
            'type'      => 'action',
            'getter'    => 'getId',
            'width'     => 80,
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'type',
            'link_type' => 'actions',
            'actions'   => array(
                array(
                    'url'       => $this->getUrl('*/*/edit', array('id' => '$action_id')),
                    'caption'   => Mage::helper('enterprise_staging')->__('Edit')
                ),
                array(
                    'url'       => $this->getUrl('*/*/delete', array('id' => '$action_id')),
                    'caption'   => Mage::helper('enterprise_staging')->__('Delete'),
                    'confirm'   => Mage::helper('enterprise_staging')->__('Are you sure you want to do this?')
                )
            )
        ));

        return $this;
    }

    /**
     *  Prepare mass action block
     *
     * @return Enterprise_Staging_Block_Manage_Staging_Backup_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('action_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->setNoFilterMassactionColumn(true);
        $this->getMassactionBlock()->setFormFieldName('backupDelete');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('enterprise_staging')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('enterprise_staging')->__('Are you sure?')
        ));
        return $this;
    }


    /**
     * prepare used website list
     *
     * @return array
     */
    protected function _getWebsiteList()
    {
        $collection = $this->getCollection();

        $websites = array();

        foreach($collection as $backup) {
            $websiteId   = $backup->getMasterWebsiteId();
            $websiteName = $backup->getMasterWebsiteName();
            $websites[$websiteId] = $websiteName;
        }

        return $websites;
    }

    /**
     * Return grids url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * Return grid row url
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('_current'=>true, 'id'=>$row->getId()));
    }

    /**
     * Prepare action/backup collection
     * used in such way instead of standard _prepareCollection
     * bc we need collection preloaded in _prepareColumns
     *
     * @return Enterprise_Staging_Model_Mysql4_Staging_Action_Collection
     */
    public function getCollection()
    {
        if (!$this->hasData('collection')) {
            $collection = Mage::getResourceModel('enterprise_staging/staging_action_collection')
                ->addFieldToFilter('type', 'backup')
                ->addWebsitesToCollection();
            $this->setData('collection', $collection);
        }
        return $this->getData('collection');
    }
}
