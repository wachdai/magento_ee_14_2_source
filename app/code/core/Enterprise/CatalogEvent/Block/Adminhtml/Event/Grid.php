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
 * @package     Enterprise_CatalogEvent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Catalog Events grid
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogEvent
 */
class Enterprise_CatalogEvent_Block_Adminhtml_Event_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('catalogEventGrid');
        $this->setDefaultSort('event_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepares events collection
     *
     * @return Enterprise_CatalogEvent_Block_Adminhtml_Event_Grid
     */
    protected function _prepareCollection()
    {
           $collection = Mage::getModel('enterprise_catalogevent/event')->getCollection()
               ->addCategoryData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare event grid columns
     *
     * @return Enterprise_CatalogEvent_Block_Adminhtml_Event_Grid
     */
    protected function _prepareColumns()
    {

        $this->addColumn('event_id', array(
            'header' => Mage::helper('enterprise_catalogevent')->__('ID'),
            'width'  => '80px',
            'type'   => 'text',
            'index'  => 'event_id'
        ));

        $this->addColumn('category_id', array(
            'header' => Mage::helper('enterprise_catalogevent')->__('Category ID'),
            'index' => 'category_id',
            'type'  => 'text',
            'width' => 70
        ));

        $this->addColumn('category', array(
            'header' => Mage::helper('enterprise_catalogevent')->__('Category'),
            'index' => 'category_name',
            'type'  => 'text'
        ));

        $this->addColumn('date_start', array(
            'header' => Mage::helper('enterprise_catalogevent')->__('Starts On'),
            'index' => 'date_start',
            'type' => 'datetime',
            'filter_time' => true,
            'width' => 150
        ));

        $this->addColumn('date_end', array(
            'header' => Mage::helper('enterprise_catalogevent')->__('Ends On'),
            'index' => 'date_end',
            'type' => 'datetime',
            'filter_time' => true,
            'width' => 150
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('enterprise_catalogevent')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                Enterprise_CatalogEvent_Model_Event::STATUS_UPCOMING => Mage::helper('enterprise_catalogevent')
                    ->__('Upcoming'),
                Enterprise_CatalogEvent_Model_Event::STATUS_OPEN => Mage::helper('enterprise_catalogevent')->__('Open'),
                Enterprise_CatalogEvent_Model_Event::STATUS_CLOSED =>
                    Mage::helper('enterprise_catalogevent')->__('Closed')
            ),
            'width' => 140
        ));

        $this->addColumn('display_state', array(
            'header' => Mage::helper('enterprise_catalogevent')->__('Display Countdown Ticker On'),
            'index' => 'display_state',
            'type' => 'options',
            'renderer' => 'enterprise_catalogevent/adminhtml_event_grid_column_renderer_bitmask',
            'options' => array(
                0 => Mage::helper('enterprise_catalogevent')->__('Lister Block'),
                Enterprise_CatalogEvent_Model_Event::DISPLAY_CATEGORY_PAGE =>
                    Mage::helper('enterprise_catalogevent')->__('Category Page'),
                Enterprise_CatalogEvent_Model_Event::DISPLAY_PRODUCT_PAGE =>
                    Mage::helper('enterprise_catalogevent')->__('Product Page')
            )
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('enterprise_catalogevent')->__('Sort Order'),
            'index' => 'sort_order',
            'type'  => 'text',
            'width' => 70
        ));

        $this->addColumn('actions', array(
            'header'    => $this->helper('enterprise_catalogevent')->__('Action'),
            'width'     => 15,
            'sortable'  => false,
            'filter'    => false,
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => $this->helper('enterprise_catalogevent')->__('Edit'),
                    'url'     => array('base'=>'*/*/edit'),
                    'field'   => 'id'
                )
            ),
        ));

        return parent::_prepareColumns();
    }


    /**
     * Grid row event edit url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
