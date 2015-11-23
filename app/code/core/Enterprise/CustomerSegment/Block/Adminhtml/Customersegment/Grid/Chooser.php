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
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer Segment grid
 *
 * @category   Enterprise
 * @package    Enterprise_CustomerSegment
 */
class Enterprise_CustomerSegment_Block_Adminhtml_Customersegment_Grid_Chooser
    extends Enterprise_CustomerSegment_Block_Adminhtml_Customersegment_Grid
{
    /**
     * Intialize grid
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('customersegment_grid_chooser_'.$this->getId());
        }

        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);

        $form = $this->getRequest()->getParam('form');
        if ($form) {
            $this->setRowClickCallback("$form.chooserGridRowClick.bind($form)");
            $this->setCheckboxCheckCallback("$form.chooserGridCheckboxCheck.bind($form)");
            $this->setRowInitCallback("$form.chooserGridRowInit.bind($form)");
        }
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Row click javasctipt callback getter
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        return $this->_getData('row_click_callback');
    }

    /**
     * Prepare columns for grid
     *
     * @return Enterprise_CustomerSegment_Block_Adminhtml_Customersegment_Grid_Chooser
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_segments', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_segments',
            'values'    => $this->_getSelectedSegments(),
            'align'     => 'center',
            'index'     => 'segment_id',
            'use_index' => true,
        ));
        return parent::_prepareColumns();
    }

    /**
     * Get Selected ids param from request
     *
     * @return array
     */
    protected function _getSelectedSegments()
    {
        $segments = $this->getRequest()->getPost('selected', array());
        return $segments;
    }

    /**
     * Grid URL getter for ajax mode
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/customersegment/chooserGrid', array('_current' => true));
    }
}
