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

class Enterprise_Support_Block_Adminhtml_Sysreport_View_Tab_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set defaults
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    /**
     * Prevent appending grid JS so it will not be loaded (it is not needed)
     *
     * @return bool
     */
    public function canDisplayContainer()
    {
        return false;
    }

    /**
     * Return row url for js event handlers
     *
     * @param Mage_Catalog_Model_Product|Varien_Object
     * @return string
     */
    public function getRowUrl($item)
    {
        return '';
    }

    /**
     * Instantiate and prepare collection
     *
     * @return Enterprise_Banner_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();
        $gridData = $this->getGridData();
        if (!$this->hasGridData() || empty($gridData) || !is_array($gridData)
            || empty($gridData['header']) || empty($gridData['data'])
        ) {
            $this->setCollection($collection);
            return $this;
        }

        foreach ($gridData['data'] as $dataValues) {
            $itemObject = new Varien_Object();
            foreach ($dataValues as $valueIndex => $value) {
                $itemObject->setData($this->_getColumnId($gridData['header'][$valueIndex]), $value);
            }
            $collection->addItem($itemObject);

        }
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Define grid columns
     */
    protected function _prepareColumns()
    {
        $gridData = $this->getGridData();
        if (!$this->hasGridData() || empty($gridData) || !is_array($gridData)
            || empty($gridData['header']) || empty($gridData['data'])
        ) {
            parent::_prepareColumns();
            return $this;
        }

        /** @var Enterprise_Support_Helper_Data $helper */
        $helper = Mage::helper('enterprise_support');

        foreach ($gridData['header'] as $columnLabel) {
            $this->addColumn($this->_getColumnId($columnLabel),
                array(
                    'header' => $helper->getReportColumnLabel($columnLabel),
                    'align' => 'left',
                    'index' => $this->_getColumnId($columnLabel),
                    'sortable' => false,
                    'renderer' => 'Enterprise_Support_Block_Adminhtml_Sysreport_View_Tab_Grid_Column_Renderer_Text',
                    'filter' => false
                ));
        }

        parent::_prepareColumns();
        return $this;
    }

    /**
     * Get column id text by column name
     *
     * @param string $column
     *
     * @return string
     */
    protected function _getColumnId($column)
    {
        $column = preg_replace('/[^\p{L}\p{N}\s]/u', '', $column);
        $column = strtolower(str_replace(' ', '_', $column));
        if ($column == 'id') {
            $column .= 'value';
        }

        return $column;
    }
}
