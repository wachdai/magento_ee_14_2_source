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

class Enterprise_Support_Block_Adminhtml_Sysreport_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Set Block ID
     * Set Destination Element ID
     * Set Title
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setId('sysreport_info_tabs');
        $this->setDestElementId('view_form');
        $this->setTitle(Mage::helper('enterprise_support')->__('System Report Information'));
    }

    protected function _prepareLayout()
    {
        /** @var Enterprise_Support_Model_Sysreport $sysreport */
        $sysreport = $this->getSystemReport();
        $reportData = $sysreport->prepareSysReportData();
        if (!$reportData) {
            Mage::throwException(
                Mage::helper('enterprise_support')->__('Requested system report has no data to display.')
            );
        }

        $installedSysReportTypes = Mage::helper('enterprise_support')->getSysReportTypes();
        foreach ($installedSysReportTypes as $typeName => $typeConfig) {
            $needToAddTab = false;
            $gridsData = array();
            foreach ($typeConfig['commands'] as $command) {
                if (!array_key_exists($command, $reportData)) {
                    continue;
                }
                $needToAddTab = true;
                $gridsData[$command] = $reportData[$command];
            }

            if ($needToAddTab) {
                /** @var Enterprise_Support_Block_Adminhtml_Sysreport_View_Tab $block */
                $block = $this->getLayout()->createBlock('enterprise_support/adminhtml_sysreport_view_tab');
                $block->setGridsData($gridsData);

                $this->addTab($typeName, array(
                    'label'   => $typeConfig['title'],
                    'content' => $block->toHtml(),
                ));
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve system report object
     *
     * @return Enterprise_Support_Model_Resource_Sysreport
     */
    public function getSystemReport()
    {
        if (!($this->getData('system_report') instanceof Enterprise_Support_Model_Resource_Sysreport)) {
            $this->setData('system_report', Mage::registry('current_sysreport'));
        }
        return $this->getData('system_report');
    }
}
