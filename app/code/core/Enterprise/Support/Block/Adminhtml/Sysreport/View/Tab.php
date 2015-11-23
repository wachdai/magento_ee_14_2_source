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

class Enterprise_Support_Block_Adminhtml_Sysreport_View_Tab extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Data helper object
     *
     * @var Enterprise_Support_Helper_Data
     */
    protected $_dataHelper = null;

    /**
     * Set template file, initialize data helper
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setTemplate('enterprise/support/sysreport/view/tab.phtml');
        /** @var Enterprise_Support_Helper_Data $this->_dataHelper */
        $this->_dataHelper = Mage::helper('enterprise_support');
    }

    /**
     * Retrieve system report grid blocks
     *
     * @return array
     */
    public function getGrids()
    {
        $grids = array();
        $gridsData = $this->getGridsData();
        if (!$this->hasGridsData() || empty($gridsData) || !is_array($gridsData)) {
            return $grids;
        }
        foreach ($this->getGridsData() as $reports) {
            foreach ($reports as $title => $data) {
                $grids[$title] = new Varien_Object();
                if (!empty($data['error'])) {
                    $grids[$title]->setDataCount(0);
                    $grids[$title]->setError($data['error']);
                    continue;
                }

                /** @var Enterprise_Support_Block_Adminhtml_Sysreport_View_Tab_Grid $block */
                $block = $this->getLayout()->createBlock('enterprise_support/adminhtml_sysreport_view_tab_grid');
                $block->setId('grid_' . md5($title))
                    ->setGridData($data);
                $grids[$title]->setDataCount($this->_dataHelper->getSysReportDataCount($data));
                $grids[$title]->setGridObject($block);
            }
        }

        return $grids;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('enterprise_support')->__('Report');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
