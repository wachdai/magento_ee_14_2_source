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

class Enterprise_Support_Block_Adminhtml_Sysreport_Export_Html extends Mage_Adminhtml_Block_Template
{
    /**
     * Determine weather to display template hints
     *
     * @var bool
     */
    protected static $_showTemplateHints = false;

    /**
     * Determine weather to display template block hints
     *
     * @var bool
     */
    protected static $_showTemplateHintsBlocks = false;

    /**
     * List of reports by types
     *
     * @var null|array
     */
    protected $_reportByTypes = null;

    /**
     * Set template file
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->setTemplate('enterprise/support/sysreport/export/html.phtml');
    }

    /**
     * Collect, prepare and retrieve report data by types
     *
     * @return array
     */
    public function getReportByTypes()
    {
        /** @var Enterprise_Support_Model_Sysreport $sysreport */
        $sysreport = $this->getSystemReport();
        if (!$sysreport->getId()) {
            return array();
        }

        if ($this->_reportByTypes !== null) {
            return $this->_reportByTypes;
        }

        $reportData = $sysreport->prepareSysReportData();
        if (!$reportData) {
            Mage::throwException(
                Mage::helper('enterprise_support')->__('Requested system report has no data to output.')
            );
        }

        $this->_reportByTypes = array();
        $installedSysReportTypes = Mage::helper('enterprise_support')->getSysReportTypes();
        foreach ($installedSysReportTypes as $typeName => $typeConfig) {
            foreach ($typeConfig['commands'] as $command) {
                if (!array_key_exists($command, $reportData)) {
                    continue;
                }

                if (!isset($this->_reportByTypes[$typeName])) {
                    $this->_reportByTypes[$typeName] = array('title' => $typeConfig['title'], 'reports' => array());
                }
                $this->_reportByTypes[$typeName]['reports'] = array_merge(
                    $this->_reportByTypes[$typeName]['reports'],
                    $reportData[$command]
                );
            }
        }

        return $this->_reportByTypes;
    }

    /**
     * Retrieve system report title
     *
     * @return string
     */
    public function getSysReportTitle()
    {
        /** @var Enterprise_Support_Model_Sysreport $sysreport */
        $sysreport = $this->getSystemReport();
        if (!$sysreport->getId()) {
            return '';
        }

        return $sysreport->getClientHost();
    }

    /**
     * Retrieve system report created at date
     *
     * @return string
     */
    public function getSysReportCreateDate()
    {
        /** @var Enterprise_Support_Model_Sysreport $sysreport */
        $sysreport = $this->getSystemReport();
        if (!$sysreport->getId()) {
            return '';
        }

        return $sysreport->getCreatedAt();
    }

    /**
     * Retrieve copyright text
     *
     * @return string
     */
    public function getCopyrightText()
    {
        /** @var Enterprise_Support_Model_Sysreport $sysreport */
        $sysreport = $this->getSystemReport();
        if (!$sysreport->getId()) {
            return '';
        }

        $version = $this->escapeHtml($sysreport->getReportVersion());
        $text = Mage::helper('enterprise_support')->__('&copy; Magento Inc., %s<br />v%s', date('Y'), $version);
        return $text;
    }

    /**
     * Retrieve language code
     *
     * @return string|null
     */
    public function getLang()
    {
        if (!$this->hasData('lang')) {
            $this->setData('lang', substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2));
        }
        return $this->getData('lang');
    }
}
