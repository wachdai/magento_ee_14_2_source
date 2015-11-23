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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Admin Actions Log Archive grid
 *
 */
class Enterprise_Logging_Block_Adminhtml_Details_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize default sorting and html ID
     */
    protected function _construct()
    {
        $this->setId('loggingDetailsGrid');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Prepare grid collection
     *
     * @return Enterprise_Logging_Block_Events_Archive_Grid
     */
    protected function _prepareCollection()
    {
        $event = Mage::registry('current_event');
        $collection = Mage::getResourceModel('enterprise_logging/event_changes_collection')
            ->addFieldToFilter('event_id', $event->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_Logging_Block_Events_Archive_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('source_name', array(
            'header'    => Mage::helper('enterprise_logging')->__('Source Data'),
            'sortable'  => false,
            'renderer'  => 'enterprise_logging/adminhtml_details_renderer_sourcename',
            'index'     => 'source_name',
            'width'     => 1
        ));

        $this->addColumn('original_data', array(
            'header'    => Mage::helper('enterprise_logging')->__('Value Before Change'),
            'sortable'  => false,
            'renderer'  => 'enterprise_logging/adminhtml_details_renderer_diff',
            'index'     => 'original_data'
        ));

        $this->addColumn('result_data', array(
            'header'    => Mage::helper('enterprise_logging')->__('Value After Change'),
            'sortable'  => false,
            'renderer'  => 'enterprise_logging/adminhtml_details_renderer_diff',
            'index'     => 'result_data'
        ));

        return parent::_prepareColumns();
    }
}
