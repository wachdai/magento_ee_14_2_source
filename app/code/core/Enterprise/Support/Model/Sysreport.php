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

class Enterprise_Support_Model_Sysreport extends Mage_Core_Model_Abstract
{
    /**
     * Set resource model
     */
    protected function _construct()
    {
        $this->_init('enterprise_support/sysreport');
    }

    /**
     * Prepare system report data for output in HTML format
     *
     * @return bool|array
     */
    public function prepareSysReportData()
    {
        $reportData = $this->getReportData();
        if (!$this->hasReportData() || empty($reportData) || !is_array($reportData)) {
            return false;
        }

        /** @var Enterprise_Support_Helper_Data $helper */
        $helper = Mage::helper('enterprise_support');
        $preparedData = array();
        foreach ($reportData as $command => $reports) {
            if (!is_array($reports) || empty($reports)) {
                continue;
            }
            foreach ($reports as $title => $data) {
                try {
                    if (!empty($data['data'])) {
                        $info = $helper->prepareSysReportTableData(
                            $data['data'],
                            !empty($data['header']) ? $data['header'] : array()
                        );
                    } else {
                        $info = array('header' => array(), 'data' => array());
                    }
                    $preparedData[$command][$title] = $info;
                } catch (Exception $e) {
                    $preparedData[$command][$title] = array('error' => $e->getMessage());
                    Mage::logException($e);
                }
            }
        }

        return $preparedData;
    }

    /**
     * Retrieve file name for system report download action
     *
     * @return string
     */
    public function getFileNameForSysreportDownload()
    {
        if (!$this->getId()) {
            return '';
        }
        $host = $this->getClientHost();
        $host = preg_replace('~[^-_.a-z0-9]+~', '', $host);
        $createdDate = $this->getCreatedAt();
        $createdDate = preg_replace('~[^-0-9]+~', '-', $createdDate);

        return 'sysreport-' . $createdDate . ($host ? '_' . $host : '') . '.html';
    }
}
