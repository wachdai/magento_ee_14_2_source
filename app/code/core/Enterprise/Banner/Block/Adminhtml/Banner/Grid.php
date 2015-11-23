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
 * @package     Enterprise_Banner
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Banner_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set defaults
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('banner_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('banner_filter');
    }

    /**
     * Instantiate and prepare collection
     *
     * @return Enterprise_Banner_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('enterprise_banner/banner_collection')->addStoresVisibility();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn('banner_id',
            array(
                'header'=> Mage::helper('enterprise_banner')->__('ID'),
                'width' => 1,
                'type'  => 'number',
                'index' => 'banner_id',
        ));

        $this->addColumn('banner_name', array(
            'header' => Mage::helper('enterprise_banner')->__('Banner Name'),
            'type'   => 'text',
            'index'  => 'name',
            'escape' => true
        ));

        $this->addColumn('banner_types', array(
            'header'  => Mage::helper('enterprise_banner')->__('Banner Types'),
            'type'    => 'options',
            'options' => Mage::getSingleton('enterprise_banner/config')->toOptionArray(true, false),
            'index'   => 'types',
            'width'   => 250,
            'filter'  => false, // TODO implement
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('visible_in', array(
                'header'                => Mage::helper('enterprise_banner')->__('Visible In'),
                'type'                  => 'store',
                'index'                 => 'stores',
                'sortable'              => false,
                'store_view'            => true,
                'width'                 => 200
            ));
        }

        $this->addColumn('banner_is_enabled',
            array(
                'header'    => Mage::helper('enterprise_banner')->__('Active'),
                'align'     => 'center',
                'width'     => 1,
                'index'     => 'is_enabled',
                'type'      => 'options',
                'options'   => array(
                    Enterprise_Banner_Model_Banner::STATUS_ENABLED  => Mage::helper('enterprise_banner')->__('Yes'),
                    Enterprise_Banner_Model_Banner::STATUS_DISABLED => Mage::helper('enterprise_banner')->__('No'),
                ),
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action options for this grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('banner');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('enterprise_banner')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('enterprise_banner')->__('Are you sure you want to delete these banners?')
        ));

        return $this;
    }

    /**
     * Grid row URL getter
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getBannerId()));
    }

    /**
     * Define row click callback
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * Add store filter
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column  $column
     * @return Enterprise_Banner_Block_Adminhtml_Banner_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getIndex() == 'stores') {
            $this->getCollection()->addStoreFilter($column->getFilter()->getCondition(), false);
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}
