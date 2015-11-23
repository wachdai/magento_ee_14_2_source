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

class Enterprise_Support_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config path to system report supported types
     */
    const XML_PATH_SYSREPORT_TYPES = 'global/sysreport/types';

    const XML_OUTPUT_PATH  = 'enterprise_support/output_path';
    const XML_SCRIPTS_PATH = 'enterprise_support/scripts_path';

    const XML_BACKUP_ITEMS = 'enterprise_support/backup_items';

    const OS_WIN_CODE = 'WIN';
    const OS_OSX_CODE = 'DAR';

    /**
     * System Report Data Limitations
     */
    const SYSREPORT_DATA_MAX_ROW_MAXIMUM_COUNT_FOR_OUTPUT = 1000;
    const SYSREPORT_DATA_MAX_COLUMN_COUNT_FOR_OUTPUT = 20;
    const SYSREPORT_DATA_MAX_COLUMN_DATA_LENGTH = 120;
    const SYSREPORT_DATA_MAX_NONE_COLLAPSIBLE_ROW_COUNT = 64;
    const SYSREPORT_DATA_MAX_NONE_COLLAPSIBLE_CELL_STRING_LENGTH = 1000;

    /**
     * System report type to label map, used to cache map
     *
     * @var null|array
     */
    protected $_sysreportTypeToLabelMap = null;

    /**
     * Store system report type list
     *
     * @var null|array
     */
    protected $_sysreportTypes = null;

    /**
     * Caching translated system report data titles, labels and values
     *
     * @var array
     */
    protected $_sysreportTranslatedTitles = null;
    protected $_sysreportTranslatedLabels = null;
    protected $_sysreportTranslatedValues = null;
    protected $_sysreportTranslatedReplaceValues = null;

    /**
     * Get Backup Items from definition on config
     *
     * @return array
     */
    public function getBackupItems()
    {
        $objectItems = array();
        $items = (array) Mage::app()->getStore()->getConfig(self::XML_BACKUP_ITEMS);
        foreach ($items as $key => $item) {
            $objectItems[$key] = Mage::getModel($item['class'], $item['params']);
        }

        return $objectItems;
    }

    /**
     * Get output path
     *
     * @return string
     */
    public function getOutputPath()
    {
        $path = Mage::getBaseDir() . DIRECTORY_SEPARATOR . Mage::app()->getStore()->getConfig(self::XML_OUTPUT_PATH);
        if (!file_exists($path)) {
            Mage::getSingleton('varien/io_file')->mkdir($path);
        }
        return $path;
    }

    /**
     * Get path where shell scripts placed
     *
     * @return null|string
     */
    public function getScriptsPath()
    {
        $path = Mage::getBaseDir() . DIRECTORY_SEPARATOR . Mage::app()->getStore()->getConfig(self::XML_SCRIPTS_PATH);
        return $path;
    }

    /**
     * Get Item Path
     *
     * @param $itemName
     *
     * @return string
     */
    public function getFilePath($itemName)
    {
        return $this->getOutputPath() . $itemName;
    }

    /**
     * Check file is locked
     *
     * @param $filePath
     *
     * @return bool
     */
    public function isFileLocked($filePath)
    {
        $result = (bool) exec('lsof ' . $filePath);
        return $result;
    }

    /**
     * Get Status Options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        $_statuses = array(
            Enterprise_Support_Model_Backup::STATUS_PROCESSING => $this->__('Incomplete'),
            Enterprise_Support_Model_Backup::STATUS_COMPLETE   => $this->__('Complete'),
            Enterprise_Support_Model_Backup::STATUS_FAILED     => $this->__('Failed')
        );

        return $_statuses;
    }

    /**
     * Get Item Status Label
     *
     * @param $item
     *
     * @return string
     */
    public function getItemStatusLabel($item)
    {
        $result = $this->__('Unknown Status');
        $processingStatus = $this->__('Processing ...');
        $_statuses = array(
            Enterprise_Support_Model_Backup_Item_Abstract::STATUS_PROCESSING => $processingStatus,
            Enterprise_Support_Model_Backup_Item_Abstract::STATUS_COMPLETE   => $this->getLinkHtml($item)
        );

        if (isset($_statuses[$item->getStatus()])) {
            $result = $_statuses[$item->getStatus()];
        }

        return $result;
    }

    /**
     * Return Html Link
     *
     * @param $item
     *
     * @return string
     */
    public function getLinkHtml($item)
    {
        $params = array('backup_id' => $item->getBackupId(), 'type' => $item->getType());
        $linkHref = Mage::helper('adminhtml')->getUrl('*/*/download', $params);
        $link = sprintf('<a href="%s" title="%s">%s</a> (%s)',
            $linkHref, $item->getName(), $item->getName(), $this->formatBytes($item->getSize()));

        return $link;
    }

    /**
     * Format Bytes to human name
     *
     * @param $bytes
     * @param int $decimals
     *
     * @return string
     */
    public function formatBytes($bytes, $decimals = 2)
    {
        $result = $bytes;
        $sizes = array('B', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
        $factor = floor((strlen($bytes) - 1) / 3);
        if (isset($sizes[$factor])) {
            $result = sprintf("%.${decimals}f", $bytes / pow(1024, $factor)) . $sizes[$factor];
        }

        return $result;
    }

    /**
     * Return Unsupported OS
     *
     * @return string
     */
    public function getUnsupportedOs()
    {
        $result = '';
        $os = array(
            self::OS_WIN_CODE => $this->__('Windows'),
            self::OS_OSX_CODE => $this->__('OS X'),
        );

        foreach ($os as $osCode => $osName) {
            if (stristr(PHP_OS, $osCode)) {
                $result = $osName;
            }
        }

        return $result;
    }

    /**
     * Check if php can run bash script
     *
     * @return bool
     */
    public function isExecEnabled()
    {
        $result = true;
        $disabledFunctions = explode(',', ini_get('disable_functions'));

        if (!function_exists('exec')) {
            $result = false;
        }

        if (in_array('exec', array_map('trim', $disabledFunctions))) {
            $result = false;
        }

        if (ini_get('safe_mode') != false) {
            $result = false;
        }

        return $result;
    }

    /**
     * Retrieve system report types config sorted by priority
     *
     * @return array|null
     */
    public function getSysReportTypes()
    {
        if ($this->_sysreportTypes === null) {
            $sysReportTypes = Mage::getConfig()->getNode(self::XML_PATH_SYSREPORT_TYPES)->asArray();
            foreach ($sysReportTypes as $type => $typeConfig) {
                $sysReportTypes[$type]['title'] = Mage::helper('enterprise_support')->__($typeConfig['title']);
                $sysReportTypes[$type]['commands'] = array_map('trim', explode(',', $typeConfig['commands']));
            }
            $this->_sysreportTypes = $sysReportTypes;
            uasort($this->_sysreportTypes, array(__CLASS__, 'typePriorityCompare'));
        }

        return $this->_sysreportTypes;
    }

    /**
     * Compare method used to sort system report types
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return array
     */
    public function typePriorityCompare($a, $b)
    {
        return $a['priority'] > $b['priority'];
    }

    /**
     * Generate system report types as options for multiselect form field
     *
     * @return array
     */
    public function getSysReportTypeOptions()
    {
        $types = $this->getSysReportTypes();
        $options = array();
        foreach ($types as $name => $config) {
            $options[] = array(
                'label' => $config['title'],
                'value' => $name,
            );
        }

        return $options;
    }

    /**
     * Retrieve supported system report types
     *
     * @return array
     */
    public function getSysReportTypeNames()
    {
        return array_keys($this->getSysReportTypes());
    }

    /**
     * Retrieve system report type to label map
     *
     * @return array
     */
    public function getSysReportTypeToLabelMap()
    {
        if ($this->_sysreportTypeToLabelMap === null) {
            $options = $this->getSysReportTypeOptions();
            foreach ($options as $option) {
                $this->_sysreportTypeToLabelMap[$option['value']] = $option['label'];
            }
        }

        return $this->_sysreportTypeToLabelMap;
    }

    /**
     * Generate system report commands to run by specified report types
     *
     * @param array|string $requestedTypes
     *
     * @return array
     */
    public function getSysReportCommandsListByReportTypes($requestedTypes)
    {
        if (!$requestedTypes) {
            return array();
        }
        if (!is_array($requestedTypes)) {
            $requestedTypes = array_map('trim', explode(',', $requestedTypes));
        }
        $existingTypes = $this->getSysReportTypes();
        $commands = array();
        foreach ($existingTypes as $name => $config) {
            if (!in_array($name, $requestedTypes) || empty($config['commands'])) {
                continue;
            }
            $commands = array_merge($commands, $config['commands']);
        }
        $commands = array_unique($commands);

        return $commands;
    }

    /**
     * Prepare system report data for output in CLI or HTML format
     *
     * @param array $data
     * @param array $header
     *
     * @return array|null
     * @throws Exception
     */
    public function prepareSysReportTableData(array $data, $header = array())
    {
        if (empty($data)) {
            return null;
        }

        $_columnSizes = $_preparedHeader = array();
        $_maxColNum = $colNum = 0;

        /**
         * Prepare header if applicable
         * Save header columns sizes
         */
        if (!empty($header)) {
            foreach ($header as $key => $column) {
                $colNum++;
                $_trimmedColumn = trim($column);

                // If maximum column number limit reached then set last column as "And more..." string
                if ($colNum == self::SYSREPORT_DATA_MAX_COLUMN_COUNT_FOR_OUTPUT + 1) {
                    $column = Mage::helper('enterprise_support')->__('And more...');
                }
                // If column data is not empty string then use it as column title
                // Otherwise "Column N" string will be used as title
                elseif (!is_null($column) && !is_bool($column) && empty($_trimmedColumn)) {
                    $column = Mage::helper('enterprise_support')->__('Column %s', $colNum);
                }

                $column = $this->_prepareSysReportTableColumnData($column);
                // Set initial column sizes
                $_columnSizes[$colNum - 1] = mb_strlen($column, 'UTF-8');
                $_preparedHeader[$key] = $column;

                // If maximum column number limit reached then stop further row data processing
                if ($colNum == self::SYSREPORT_DATA_MAX_COLUMN_COUNT_FOR_OUTPUT + 1) {
                    break;
                }
            }
            $_maxColNum = $colNum;
        }

        $_preparedData = array();
        $dataRowsCount = sizeof($data);
        $detectedSingleRowDataMode = $detectedNormalRowDataMode = $detectedMaximumDataLimit = false;
        $singleRowColNum = 0;

        /**
         * Data validation and preparation
         * Collect column data sizes
         */
        for ($rowIndex = 0; $rowIndex < $dataRowsCount; $rowIndex++) {
            $origRow = $data[$rowIndex];
            $newRow = array();
            $colNum = 0;

            if (is_array($origRow)) {
                if ($detectedSingleRowDataMode === true) {
                    Mage::throwException(
                        Mage::helper('enterprise_support')->__('Preparing system report data: Detected Single Row Mode but data may be incomplete.')
                    );
                }

                $detectedNormalRowDataMode = true;

                // If maximum row count limit reached then set last row as "And more..." string
                if ($rowIndex == self::SYSREPORT_DATA_MAX_ROW_MAXIMUM_COUNT_FOR_OUTPUT) {
                    $origRow = array(Mage::helper('enterprise_support')->__('And more...'));
                    $detectedMaximumDataLimit = true;
                }

                foreach ($origRow as $key => $column) {
                    $colNum++;
                    // If maximum column number limit reached then set last column as "And more..." string
                    if ($colNum == self::SYSREPORT_DATA_MAX_COLUMN_COUNT_FOR_OUTPUT + 1) {
                        $column = Mage::helper('enterprise_support')->__('And more...');
                    }

                    $column = $this->_prepareSysReportTableColumnData($column);
                    $newRow[$key] = $column;

                    // Detect maximum column data length, take into account multi line columns that will be split
                    $length = 0;
                    $_pregSplit = preg_split("~[\n\r]+~", $column);
                    foreach ($_pregSplit as $_line) {
                        $_length = mb_strlen($_line, 'UTF-8');
                        if ($_length > $length) {
                            $length = $_length;
                        }
                    }

                    if ((isset($_columnSizes[$colNum - 1]) && $_columnSizes[$colNum - 1] < $length)
                        || !isset($_columnSizes[$colNum - 1])
                    ) {
                        $_columnSizes[$colNum - 1] = $length;
                    }

                    // If maximum column number limit reached then stop further row data processing
                    if ($colNum == self::SYSREPORT_DATA_MAX_COLUMN_COUNT_FOR_OUTPUT + 1) {
                        break;
                    }
                }

                // Detect maximum column number in one row
                if ($colNum > $_maxColNum) {
                    $_maxColNum = $colNum;
                }
                $_preparedData[$rowIndex] = $newRow;

            } else {
                if ($detectedNormalRowDataMode) {
                    Mage::throwException(
                        Mage::helper('enterprise_support')->__('Preparing system report data: Detected Single Row Mode but data may be incomplete.')
                    );
                }

                $detectedSingleRowDataMode = true;

                if ($rowIndex == self::SYSREPORT_DATA_MAX_COLUMN_COUNT_FOR_OUTPUT) {
                    $origRow = Mage::helper('enterprise_support')->__('And more...');
                    $detectedMaximumDataLimit = true;
                }

                $column =  $this->_prepareSysReportTableColumnData($origRow);
                $_preparedData[0][] = $column;

                // Detect maximum column data length
                $length = mb_strlen($column, 'UTF-8');
                if ((isset($_columnSizes[$singleRowColNum]) && $_columnSizes[$singleRowColNum] < $length)
                    || !isset($_columnSizes[$singleRowColNum])
                ) {
                    $_columnSizes[$singleRowColNum] = $length;
                }

                $singleRowColNum++;
            }

            // If maximum row count limit reached then stop further data processing
            if ($detectedMaximumDataLimit) {
                break;
            }
        }

        // If data was retrieved as single row then make sure that maximum column number detection has last update
        if ($singleRowColNum > $_maxColNum) {
            $_maxColNum = $singleRowColNum;
        }

        /**
         * Header array normalization
         * Save maximum column sizes
         */
        if (!empty($_preparedHeader)) {
            $_headerColCount = sizeof($_preparedHeader);
            $colNum = $_headerColCount + 1;
            if ($_headerColCount < $_maxColNum) {
                for ($counter = 0; $counter < ($_maxColNum - $_headerColCount); $counter++) {
                    $_column = Mage::helper('enterprise_support')->__('Column %s', $colNum);
                    $_preparedHeader[] = $_column;
                    $length = mb_strlen($_column, 'UTF-8');
                    if ((isset($_columnSizes[$colNum - 1]) && $_columnSizes[$colNum - 1] < $length)
                        || !isset($_columnSizes[$colNum - 1])
                    ) {
                        $_columnSizes[$colNum - 1] = $length;
                    }
                    $colNum++;
                }
            }
        }

        /**
         * Data array normalization
         * Save maximum column sizes
         */
        if ($detectedSingleRowDataMode) {
            if ($singleRowColNum < $_maxColNum) {
                $colNum = $singleRowColNum + 1;
                for ($counter = 0; $counter < ($_maxColNum - $singleRowColNum); $counter++) {
                    $_column = '';
                    $_preparedData[0][] = $_column;

                    // Only if header wasn't normalized/specified
                    if (empty($_preparedHeader)) {
                        $_columnSizes[$colNum - 1] = mb_strlen($_column, 'UTF-8');
                    }

                    $colNum++;
                }
            }
        } else {
            foreach ($_preparedData as &$row) {
                $_dataRowColCount = sizeof($row);
                $colNum = $_dataRowColCount + 1;
                if ($_dataRowColCount < $_maxColNum) {
                    for ($counter = 0; $counter < ($_maxColNum - $_dataRowColCount); $counter++) {
                        $_column = '';
                        $row[] = $_column;

                        // Only if header wasn't normalized/specified
                        if (empty($_preparedHeader)) {
                            // Detect maximum column data length
                            $length = mb_strlen($_column, 'UTF-8');
                            if (isset($_columnSizes[$colNum - 1]) && $_columnSizes[$colNum - 1] < $length) {
                                $_columnSizes[$colNum - 1] = $length;
                            }
                        }

                        $colNum++;
                    }
                }
            }
        }
        unset($row);

        /**
         * Normalization of maximum column sizes
         */
        $maxColWidth = self::SYSREPORT_DATA_MAX_COLUMN_DATA_LENGTH;
        foreach ($_columnSizes as &$size) {
            if ((!empty($maxColWidth) && $maxColWidth > 0)) {
                if ($size > $maxColWidth) {
                    $size = $maxColWidth;
                }
            }
        }

        return array('column_sizes' => $_columnSizes, 'header' => $_preparedHeader, 'data' => $_preparedData);
    }

    /**
     * Convert table column data into readable format
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function _prepareSysReportTableColumnData($data)
    {
        if (is_null($data)) {
            $data = 'null';
        } elseif (is_bool($data)) {
            $data = $data ? 'true' : 'false';
        } elseif (is_object($data)) {
            $data = 'Object ' . get_class($data);
        } elseif (is_array($data)) {
            $data = 'array(' . sizeof($data) . ')';
        } elseif (is_numeric($data)) {
            // as is
        } elseif (is_string($data)) {
            if (empty($data)) {
                $data = '';
            }
        } else {
            $data = (string)$data;
        }

        return $data;
    }

    /**
     * Retrieve system report data count
     *
     * @param array $data
     * @return int
     */
    public function getSysReportDataCount($data)
    {
        $dataNumber = 0;
        if (isset($data['data']) && is_array($data['data'])) {
            if (isset($data['count']) && $data['count'] > 0) {
                $dataNumber = $data['count'];
            } else {
                $dataNumber = sizeof($data['data']);
            }
        }

        return $dataNumber;
    }

    /**
     * Translate report table title
     *
     * @param string $title
     *
     * @return string
     */
    public function getReportTitle($title)
    {
        if ($this->_sysreportTranslatedTitles === null) {
            $this->_sysreportTranslatedTitles = array(
                'Modified Core Files'               => Mage::helper('enterprise_support')->__('Modified Core Files'),
                'Missing Core Files'                => Mage::helper('enterprise_support')->__('Missing Core Files'),
                'New Files'                         => Mage::helper('enterprise_support')->__('New Files'),
                'Patch Files List'                  => Mage::helper('enterprise_support')->__('Patch Files List'),
                'Files Permissions'                 => Mage::helper('enterprise_support')->__('Files Permissions'),
                'Modified Core Tables'              => Mage::helper('enterprise_support')->__('Modified Core Tables'),
                'Missing Core Tables'               => Mage::helper('enterprise_support')->__('Missing Core Tables'),
                'New DB Tables'                     => Mage::helper('enterprise_support')->__('New DB Tables'),
                'Custom Admin Events'               => Mage::helper('enterprise_support')->__('Custom Admin Events'),
                'Custom Global Events'              => Mage::helper('enterprise_support')->__('Custom Global Events'),
                'Enterprise Admin Events'           => Mage::helper('enterprise_support')->__('Enterprise Admin Events'),
                'Enterprise Global Events'          => Mage::helper('enterprise_support')->__('Enterprise Global Events'),
                'Core Admin Events'                 => Mage::helper('enterprise_support')->__('Core Admin Events'),
                'Core Global Events'                => Mage::helper('enterprise_support')->__('Core Global Events'),
                'All Admin Events'                  => Mage::helper('enterprise_support')->__('All Admin Events'),
                'All Global Events'                 => Mage::helper('enterprise_support')->__('All Global Events'),
                'Active Class Rewrites'             => Mage::helper('enterprise_support')->__('Active Class Rewrites'),
                'File Rewrites'                     => Mage::helper('enterprise_support')->__('File Rewrites'),
                'Controller Rewrites'               => Mage::helper('enterprise_support')->__('Controller Rewrites'),
                'Router Rewrites'                   => Mage::helper('enterprise_support')->__('Router Rewrites'),
                'Magento Version'                   => Mage::helper('enterprise_support')->__('Magento Version'),
                'All Modules List'                  => Mage::helper('enterprise_support')->__('All Modules List'),
                'Core Modules List'                 => Mage::helper('enterprise_support')->__('Core Modules List'),
                'Enterprise Modules List'           => Mage::helper('enterprise_support')->__('Enterprise Modules List'),
                'Custom Modules List'               => Mage::helper('enterprise_support')->__('Custom Modules List'),
                'Disabled Modules List'             => Mage::helper('enterprise_support')->__('Disabled Modules List'),
                'Data Count'                        => Mage::helper('enterprise_support')->__('Data Count'),
                'Configuration'                     => Mage::helper('enterprise_support')->__('Configuration'),
                'Data from app/etc/local.xml'       => Mage::helper('enterprise_support')->__('Data from app/etc/local.xml'),
                'Data from app/etc/config.xml'      => Mage::helper('enterprise_support')->__('Data from app/etc/config.xml'),
                'Data from app/etc/enterprise.xml'  => Mage::helper('enterprise_support')->__('Data from app/etc/enterprise.xml'),
                'Shipping Methods'                  => Mage::helper('enterprise_support')->__('Shipping Methods'),
                'Payment Methods'                   => Mage::helper('enterprise_support')->__('Payment Methods'),
                'Log Files'                         => Mage::helper('enterprise_support')->__('Log Files'),
                'Top System Messages'               => Mage::helper('enterprise_support')->__('Top System Messages'),
                "Today's Top System Messages"       => Mage::helper('enterprise_support')->__("Today's Top System Messages"),
                'Top Exception Messages'            => Mage::helper('enterprise_support')->__('Top Exception Messages'),
                "Today's Top Exception Messages"    => Mage::helper('enterprise_support')->__("Today's Top Exception Messages"),
                'Environment Information'           => Mage::helper('enterprise_support')->__('Environment Information'),
                'MySQL Status'                      => Mage::helper('enterprise_support')->__('MySQL Status'),
                'Cache Status'                      => Mage::helper('enterprise_support')->__('Cache Status'),
                'Index Status'                      => Mage::helper('enterprise_support')->__('Index Status'),
                'Compiler Status'                   => Mage::helper('enterprise_support')->__('Compiler Status'),
                'Cron Schedules by status code'     => Mage::helper('enterprise_support')->__('Cron Schedules by status code'),
                'Cron Schedules by job code'        => Mage::helper('enterprise_support')->__('Cron Schedules by job code'),
                'Cron Schedules List'               => Mage::helper('enterprise_support')->__('Cron Schedules List'),
                'Errors in Cron Schedules Queue'    => Mage::helper('enterprise_support')->__('Errors in Cron Schedules Queue'),
                'All Global Cron Jobs'              => Mage::helper('enterprise_support')->__('All Global Cron Jobs'),
                'All Configurable Cron Jobs'        => Mage::helper('enterprise_support')->__('All Configurable Cron Jobs'),
                'Core Global Cron Jobs'             => Mage::helper('enterprise_support')->__('Core Global Cron Jobs'),
                'Core Configurable Cron Jobs'       => Mage::helper('enterprise_support')->__('Core Configurable Cron Jobs'),
                'Enterprise Global Cron Jobs'       => Mage::helper('enterprise_support')->__('Enterprise Global Cron Jobs'),
                'Enterprise Configurable Cron Jobs' => Mage::helper('enterprise_support')->__('Enterprise Configurable Cron Jobs'),
                'Custom Global Cron Jobs'           => Mage::helper('enterprise_support')->__('Custom Global Cron Jobs'),
                'Custom Configurable Cron Jobs'     => Mage::helper('enterprise_support')->__('Custom Configurable Cron Jobs'),
                'DB Tables Status'                  => Mage::helper('enterprise_support')->__('DB Tables Status'),
                'DB MyISAM Tables Status'           => Mage::helper('enterprise_support')->__('DB MyISAM Tables Status'),
                'DB Routines List'                  => Mage::helper('enterprise_support')->__('DB Routines List'),
                'Missing DB Routines List'          => Mage::helper('enterprise_support')->__('Missing DB Routines List'),
                'New DB Routines List'              => Mage::helper('enterprise_support')->__('New DB Routines List'),
                'Websites Tree'                     => Mage::helper('enterprise_support')->__('Websites Tree'),
                'Websites List'                     => Mage::helper('enterprise_support')->__('Websites List'),
                'Stores List'                       => Mage::helper('enterprise_support')->__('Stores List'),
                'Store Views List'                  => Mage::helper('enterprise_support')->__('Store Views List'),
                'Design Themes Config'              => Mage::helper('enterprise_support')->__('Design Themes Config'),
                'Applied Solutions List'            => Mage::helper('enterprise_support')->__('Applied Solutions List'),
                'Entity Types'                      => Mage::helper('enterprise_support')->__('Entity Types'),
                'All Eav Attributes'                => Mage::helper('enterprise_support')->__('All Eav Attributes'),
                'New Eav Attributes'                => Mage::helper('enterprise_support')->__('New Eav Attributes'),
                'User Defined Eav Attributes'       => Mage::helper('enterprise_support')->__('User Defined Eav Attributes'),
                'Category Eav Attributes'           => Mage::helper('enterprise_support')->__('Category Eav Attributes'),
                'Product Eav Attributes'            => Mage::helper('enterprise_support')->__('Product Eav Attributes'),
                'Customer Eav Attributes'           => Mage::helper('enterprise_support')->__('Customer Eav Attributes'),
                'Customer Address Eav Attributes'   => Mage::helper('enterprise_support')->__('Customer Address Eav Attributes'),
                'Rma Item Eav Attributes'           => Mage::helper('enterprise_support')->__('Rma Item Eav Attributes'),
                'Payments Functionality Matrix'     => Mage::helper('enterprise_support')->__('Payments Functionality Matrix'),
                'Corrupted Categories Data'         => Mage::helper('enterprise_support')->__('Corrupted Categories Data'),
                'Duplicate Products By SKU'         => Mage::helper('enterprise_support')->__('Duplicate Products By SKU'),
                'Duplicate Products By URL Key'     => Mage::helper('enterprise_support')->__('Duplicate Products By URL Key'),
                'Duplicate Orders By Increment ID'  => Mage::helper('enterprise_support')->__('Duplicate Orders By Increment ID'),
                'Duplicate Categories by URL key'   => Mage::helper('enterprise_support')->__('Duplicate Categories by URL key'),
                'Class Rewrite Conflicts'           => Mage::helper('enterprise_support')->__('Class Rewrite Conflicts'),

                'DB Triggers List'                  => Mage::helper('enterprise_support')->__('DB Triggers List'),
                'Missing DB Triggers List'          => Mage::helper('enterprise_support')->__('Missing DB Triggers List'),
                'New DB Triggers List'              => Mage::helper('enterprise_support')->__('New DB Triggers List'),
            );
        }

        if (!empty($this->_sysreportTranslatedTitles[$title])) {
            $title = $this->_sysreportTranslatedTitles[$title];
        }

        return $title;
    }

    /**
     * Translate report table column label
     *
     * @param string $label
     * @return string
     */
    public function getReportColumnLabel($label)
    {
        if ($this->_sysreportTranslatedTitles === null) {
            $this->_sysreportTranslatedLabels = array(
                'File'                      => Mage::helper('enterprise_support')->__('File'),
                'Size'                      => Mage::helper('enterprise_support')->__('Size'),
                'Last Update'               => Mage::helper('enterprise_support')->__('Last Update'),
                'Patch'                     => Mage::helper('enterprise_support')->__('Patch'),
                'Permissions'               => Mage::helper('enterprise_support')->__('Permissions'),
                'Owner'                     => Mage::helper('enterprise_support')->__('Owner'),
                'Missing Data'              => Mage::helper('enterprise_support')->__('Missing Data'),
                'New Data'                  => Mage::helper('enterprise_support')->__('New Data'),
                'Corrupted Data'            => Mage::helper('enterprise_support')->__('Corrupted Data'),
                'Event Name'                => Mage::helper('enterprise_support')->__('Event Name'),
                'Observer Class'            => Mage::helper('enterprise_support')->__('Observer Class'),
                'Method'                    => Mage::helper('enterprise_support')->__('Method'),
                'Original Class'            => Mage::helper('enterprise_support')->__('Original Class'),
                'New Class'                 => Mage::helper('enterprise_support')->__('New Class'),
                'Type'                      => Mage::helper('enterprise_support')->__('Type'),
                'Core File'                 => Mage::helper('enterprise_support')->__('Core File'),
                'Core Pool'                 => Mage::helper('enterprise_support')->__('Core Pool'),
                'Custom Pool'               => Mage::helper('enterprise_support')->__('Custom Pool'),
                'Core Controller'           => Mage::helper('enterprise_support')->__('Core Controller'),
                'Core Action(s)'            => Mage::helper('enterprise_support')->__('Core Action(s)'),
                'Custom Controller'         => Mage::helper('enterprise_support')->__('Custom Controller'),
                'Custom Action(s)'          => Mage::helper('enterprise_support')->__('Custom Action(s)'),
                'From'                      => Mage::helper('enterprise_support')->__('From'),
                'To'                        => Mage::helper('enterprise_support')->__('To'),
                'Version'                   => Mage::helper('enterprise_support')->__('Version'),
                'Module'                    => Mage::helper('enterprise_support')->__('Module'),
                'Code Pool'                 => Mage::helper('enterprise_support')->__('Code Pool'),
                'Config Version'            => Mage::helper('enterprise_support')->__('Config Version'),
                'DB Version'                => Mage::helper('enterprise_support')->__('DB Version'),
                'DB Data Version'           => Mage::helper('enterprise_support')->__('DB Data Version'),
                'Output'                    => Mage::helper('enterprise_support')->__('Output'),
                'Enabled'                   => Mage::helper('enterprise_support')->__('Enabled'),
                'Entity'                    => Mage::helper('enterprise_support')->__('Entity'),
                'Count'                     => Mage::helper('enterprise_support')->__('Count'),
                'Extra'                     => Mage::helper('enterprise_support')->__('Extra'),
                'Name'                      => Mage::helper('enterprise_support')->__('Name'),
                'Value'                     => Mage::helper('enterprise_support')->__('Value'),
                'Scope'                     => Mage::helper('enterprise_support')->__('Scope'),
                'Path'                      => Mage::helper('enterprise_support')->__('Path'),
                'Code'                      => Mage::helper('enterprise_support')->__('Code'),
                'Title'                     => Mage::helper('enterprise_support')->__('Title'),
                'Group'                     => Mage::helper('enterprise_support')->__('Group'),
                'VIA PBridge'               => Mage::helper('enterprise_support')->__('VIA PBridge'),
                'Log Entries'               => Mage::helper('enterprise_support')->__('Log Entries'),
                'Message'                   => Mage::helper('enterprise_support')->__('Message'),
                'Last Occurrence'           => Mage::helper('enterprise_support')->__('Last Occurrence'),
                'Parameter'                 => Mage::helper('enterprise_support')->__('Parameter'),
                'Variable'                  => Mage::helper('enterprise_support')->__('Variable'),
                'Value after 10 sec'        => Mage::helper('enterprise_support')->__('Value after 10 sec'),
                'Cache'                     => Mage::helper('enterprise_support')->__('Cache'),
                'Status'                    => Mage::helper('enterprise_support')->__('Status'),
                'Associated Tags'           => Mage::helper('enterprise_support')->__('Associated Tags'),
                'Description'               => Mage::helper('enterprise_support')->__('Description'),
                'Index'                     => Mage::helper('enterprise_support')->__('Index'),
                'Update Required'           => Mage::helper('enterprise_support')->__('Update Required'),
                'Updated At'                => Mage::helper('enterprise_support')->__('Updated At'),
                'Mode'                      => Mage::helper('enterprise_support')->__('Mode'),
                'Is Visible'                => Mage::helper('enterprise_support')->__('Is Visible'),
                'State'                     => Mage::helper('enterprise_support')->__('State'),
                'Files Count'               => Mage::helper('enterprise_support')->__('Files Count'),
                'Scopes Count'              => Mage::helper('enterprise_support')->__('Scopes Count'),
                'Status Code'               => Mage::helper('enterprise_support')->__('Status Code'),
                'Schedule Id'               => Mage::helper('enterprise_support')->__('Schedule Id'),
                'Job Code'                  => Mage::helper('enterprise_support')->__('Job Code'),
                'Created At'                => Mage::helper('enterprise_support')->__('Created At'),
                'Scheduled At'              => Mage::helper('enterprise_support')->__('Scheduled At'),
                'Executed At'               => Mage::helper('enterprise_support')->__('Executed At'),
                'Finished At'               => Mage::helper('enterprise_support')->__('Finished At'),
                'Error'                     => Mage::helper('enterprise_support')->__('Error'),
                'Cron Expression'           => Mage::helper('enterprise_support')->__('Cron Expression'),
                'Run Class'                 => Mage::helper('enterprise_support')->__('Run Class'),
                'Run Method'                => Mage::helper('enterprise_support')->__('Run Method'),
                'Engine'                    => Mage::helper('enterprise_support')->__('Engine'),
                '~ Rows'                    => Mage::helper('enterprise_support')->__('~ Rows'),
                '~ Size'                    => Mage::helper('enterprise_support')->__('~ Size'),
                'Create Time'               => Mage::helper('enterprise_support')->__('Create Time'),
                'Update Time'               => Mage::helper('enterprise_support')->__('Update Time'),
                'Collation'                 => Mage::helper('enterprise_support')->__('Collation'),
                'Comment'                   => Mage::helper('enterprise_support')->__('Comment'),
                'Routine Name'              => Mage::helper('enterprise_support')->__('Routine Name'),
                'ID'                        => Mage::helper('enterprise_support')->__('ID'),
                'Root Category'             => Mage::helper('enterprise_support')->__('Root Category'),
                'Is Default'                => Mage::helper('enterprise_support')->__('Is Default'),
                'Default Store'             => Mage::helper('enterprise_support')->__('Default Store'),
                'Default Store View'        => Mage::helper('enterprise_support')->__('Default Store View'),
                'Store'                     => Mage::helper('enterprise_support')->__('Store'),
                'Date'                      => Mage::helper('enterprise_support')->__('Date'),
                'Solution'                  => Mage::helper('enterprise_support')->__('Solution'),
                'Solution Version'          => Mage::helper('enterprise_support')->__('Solution Version'),
                'Magento Version'           => Mage::helper('enterprise_support')->__('Magento Version'),
                'Reversion'                 => Mage::helper('enterprise_support')->__('Reversion'),
                'Commit'                    => Mage::helper('enterprise_support')->__('Commit'),
                'Is Gateway'                => Mage::helper('enterprise_support')->__('Is Gateway'),
                'Void'                      => Mage::helper('enterprise_support')->__('Void'),
                'For Checkout'              => Mage::helper('enterprise_support')->__('For Checkout'),
                'For Multishipping'         => Mage::helper('enterprise_support')->__('For Multishipping'),
                'Capture Online'            => Mage::helper('enterprise_support')->__('Capture Online'),
                'Partial Capture Online'    => Mage::helper('enterprise_support')->__('Partial Capture Online'),
                'Refund Online'             => Mage::helper('enterprise_support')->__('Refund Online'),
                'Partial Refund Online'     => Mage::helper('enterprise_support')->__('Partial Refund Online'),
                'Capture Offline'           => Mage::helper('enterprise_support')->__('Capture Offline'),
                'Partial Capture Offline'   => Mage::helper('enterprise_support')->__('Partial Capture Offline'),
                'Refund Offline'            => Mage::helper('enterprise_support')->__('Refund Offline'),
                'Partial Refund Offline'    => Mage::helper('enterprise_support')->__('Partial Refund Offline'),
                'Warning'                   => Mage::helper('enterprise_support')->__('Warning'),
                'Model'                     => Mage::helper('enterprise_support')->__('Model'),
                'Attribute Model'           => Mage::helper('enterprise_support')->__('Attribute Model'),
                'Increment Model'           => Mage::helper('enterprise_support')->__('Increment Model'),
                'Main Table'                => Mage::helper('enterprise_support')->__('Main Table'),
                'Additional Attribute Table'=> Mage::helper('enterprise_support')->__('Additional Attribute Table'),
                'User Defined'              => Mage::helper('enterprise_support')->__('User Defined'),
                'Entity Type Code'          => Mage::helper('enterprise_support')->__('Entity Type Code'),
                'Source Model'              => Mage::helper('enterprise_support')->__('Source Model'),
                'Backend Model'             => Mage::helper('enterprise_support')->__('Backend Model'),
                'Frontend Model'            => Mage::helper('enterprise_support')->__('Frontend Model'),
                'Expected Children Count'   => Mage::helper('enterprise_support')->__('Expected Children Count'),
                'Actual Children Count'     => Mage::helper('enterprise_support')->__('Actual Children Count'),
                'Expected Level'            => Mage::helper('enterprise_support')->__('Expected Level'),
                'Actual Level'              => Mage::helper('enterprise_support')->__('Actual Level'),
                'URL key'                   => Mage::helper('enterprise_support')->__('URL key'),
                'SKU'                       => Mage::helper('enterprise_support')->__('SKU'),
                'Increment ID'              => Mage::helper('enterprise_support')->__('Increment ID'),
                'Customer ID'               => Mage::helper('enterprise_support')->__('Customer ID'),
                'Is Active'                 => Mage::helper('enterprise_support')->__('Is Active'),
                'Email'                     => Mage::helper('enterprise_support')->__('Email'),
                'Website'                   => Mage::helper('enterprise_support')->__('Website'),
                'Factory Name'              => Mage::helper('enterprise_support')->__('Factory Name'),
                'Class'                     => Mage::helper('enterprise_support')->__('Class'),
            );
        }

        if (!empty($this->_sysreportTranslatedLabels[$label])) {
            $label = $this->_sysreportTranslatedLabels[$label];
        }

        return $label;
    }

    /**
     * Translate report table cell value
     *
     * @param string $value
     * @return string
     */
    public function getReportValueText($value)
    {
        if ($this->_sysreportTranslatedValues === null) {
            $this->_sysreportTranslatedValues = array(
                'File Name'                             => Mage::helper('enterprise_support')->__('File Name'),
                'Stores'                                => Mage::helper('enterprise_support')->__('Stores'),
                'Tax Rules'                             => Mage::helper('enterprise_support')->__('Tax Rules'),
                'Customers'                             => Mage::helper('enterprise_support')->__('Customers'),
                'Customer Attributes'                   => Mage::helper('enterprise_support')->__('Customer Attributes'),
                'Customer Segments'                     => Mage::helper('enterprise_support')->__('Customer Segments'),
                'Sales Orders'                          => Mage::helper('enterprise_support')->__('Sales Orders'),
                'Categories, Products'                  => Mage::helper('enterprise_support')->__('Categories, Products'),
                'Product Attributes'                    => Mage::helper('enterprise_support')->__('Product Attributes'),
                'URL Rewrites'                          => Mage::helper('enterprise_support')->__('URL Rewrites'),
                'URL Redirects'                         => Mage::helper('enterprise_support')->__('URL Redirects'),
                'Shopping Cart Price Rules'             => Mage::helper('enterprise_support')->__('Shopping Cart Price Rules'),
                'Catalog Price Rules'                   => Mage::helper('enterprise_support')->__('Catalog Price Rules'),
                'Target Rules'                          => Mage::helper('enterprise_support')->__('Target Rules'),
                'CMS Pages'                             => Mage::helper('enterprise_support')->__('CMS Pages'),
                'Banners'                               => Mage::helper('enterprise_support')->__('Banners'),
                'Log Visitors'                          => Mage::helper('enterprise_support')->__('Log Visitors'),
                'Log Visitors Online'                   => Mage::helper('enterprise_support')->__('Log Visitors Online'),
                'Log URLs'                              => Mage::helper('enterprise_support')->__('Log URLs'),
                'Log Quotes'                            => Mage::helper('enterprise_support')->__('Log Quotes'),
                'Log Customers'                         => Mage::helper('enterprise_support')->__('Log Customers'),
                '[Default]'                             => Mage::helper('enterprise_support')->__('[Default]'),
                'Yes'                                   => Mage::helper('enterprise_support')->__('Yes'),
                'No'                                    => Mage::helper('enterprise_support')->__('No'),
                'Base Secured URL'                      => Mage::helper('enterprise_support')->__('Base Secured URL'),
                'Base Unsecured URL'                    => Mage::helper('enterprise_support')->__('Base Unsecured URL'),
                'Base Currency'                         => Mage::helper('enterprise_support')->__('Base Currency'),
                'Enable Log'                            => Mage::helper('enterprise_support')->__('Enable Log'),
                'Log Tables Cleaning'                   => Mage::helper('enterprise_support')->__('Log Tables Cleaning'),
                'Merge JavaScript Files'                => Mage::helper('enterprise_support')->__('Merge JavaScript Files'),
                'Merge CSS Files'                       => Mage::helper('enterprise_support')->__('Merge CSS Files'),
                'Add Secret Key to URLs'                => Mage::helper('enterprise_support')->__('Add Secret Key to URLs'),
                'Flat Catalog Category'                 => Mage::helper('enterprise_support')->__('Flat Catalog Category'),
                'Flat Catalog Product'                  => Mage::helper('enterprise_support')->__('Flat Catalog Product'),
                'Fixed Product Taxes (FPT)'             => Mage::helper('enterprise_support')->__('Fixed Product Taxes (FPT)'),
                'Compilation'                           => Mage::helper('enterprise_support')->__('Compilation'),
                'Maintenance Mode'                      => Mage::helper('enterprise_support')->__('Maintenance Mode'),
                'Solr Search'                           => Mage::helper('enterprise_support')->__('Solr Search'),
                'Search Engine'                         => Mage::helper('enterprise_support')->__('Search Engine'),
                'Full Page Cache Crawler'               => Mage::helper('enterprise_support')->__('Full Page Cache Crawler'),
                'Customer Segment Functionality'        => Mage::helper('enterprise_support')->__('Customer Segment Functionality'),
                'DB Table Prefix'                       => Mage::helper('enterprise_support')->__('DB Table Prefix'),
                'Cookie Lifetime'                       => Mage::helper('enterprise_support')->__('Cookie Lifetime'),
                'Cookie Path'                           => Mage::helper('enterprise_support')->__('Cookie Path'),
                'Cookie Domain'                         => Mage::helper('enterprise_support')->__('Cookie Domain'),
                'Use HTTP Only'                         => Mage::helper('enterprise_support')->__('Use HTTP Only'),
                'Cookie Restriction Mode'               => Mage::helper('enterprise_support')->__('Cookie Restriction Mode'),
                'Validate REMOTE_ADDR'                  => Mage::helper('enterprise_support')->__('Validate REMOTE_ADDR'),
                'Validate HTTP_VIA'                     => Mage::helper('enterprise_support')->__('Validate HTTP_VIA'),
                'Validate HTTP_X_FORWARDED_FOR'         => Mage::helper('enterprise_support')->__('Validate HTTP_X_FORWARDED_FOR'),
                'Validate HTTP_USER_AGENT'              => Mage::helper('enterprise_support')->__('Validate HTTP_USER_AGENT'),
                'Use SID on Frontend'                   => Mage::helper('enterprise_support')->__('Use SID on Frontend'),
                'Remote Address'                        => Mage::helper('enterprise_support')->__('Remote Address'),
                'OS Information'                        => Mage::helper('enterprise_support')->__('OS Information'),
                'Apache Version'                        => Mage::helper('enterprise_support')->__('Apache Version'),
                'Document Root'                         => Mage::helper('enterprise_support')->__('Document Root'),
                'Server Address'                        => Mage::helper('enterprise_support')->__('Server Address'),
                'Apache Loaded Modules'                 => Mage::helper('enterprise_support')->__('Apache Loaded Modules'),
                'MySQL Server Version'                  => Mage::helper('enterprise_support')->__('MySQL Server Version'),
                'MySQL Supported Engines'               => Mage::helper('enterprise_support')->__('MySQL Supported Engines'),
                'MySQL Databases Present'               => Mage::helper('enterprise_support')->__('MySQL Databases Present'),
                'MySQL Configuration'                   => Mage::helper('enterprise_support')->__('MySQL Configuration'),
                'MySQL Plugins'                         => Mage::helper('enterprise_support')->__('MySQL Plugins'),
                'PHP Version'                           => Mage::helper('enterprise_support')->__('PHP Version'),
                'PHP Additional .ini files parsed'      => Mage::helper('enterprise_support')->__('PHP Additional .ini files parsed'),
                'PHP Loaded Config File'                => Mage::helper('enterprise_support')->__('PHP Loaded Config File'),
                'PHP Configuration'                     => Mage::helper('enterprise_support')->__('PHP Configuration'),
                'PHP Loaded Modules'                    => Mage::helper('enterprise_support')->__('PHP Loaded Modules'),
                'Enabled'                               => Mage::helper('enterprise_support')->__('Enabled'),
                'Disabled'                              => Mage::helper('enterprise_support')->__('Disabled'),
                'Never'                                 => Mage::helper('enterprise_support')->__('Never'),
                'Compiled'                              => Mage::helper('enterprise_support')->__('Compiled'),
                'Not Compiled'                          => Mage::helper('enterprise_support')->__('Not Compiled'),
                'Invalidated'                           => Mage::helper('enterprise_support')->__('Invalidated'),
                'Ready'                                 => Mage::helper('enterprise_support')->__('Ready'),
                'Processing'                            => Mage::helper('enterprise_support')->__('Processing'),
                'Reindex Required'                      => Mage::helper('enterprise_support')->__('Reindex Required'),
                'Scheduled'                             => Mage::helper('enterprise_support')->__('Scheduled'),
                'n/a'                                   => Mage::helper('enterprise_support')->__('n/a'),
                'Current Package Name'                  => Mage::helper('enterprise_support')->__('Current Package Name'),
                'Default Theme'                         => Mage::helper('enterprise_support')->__('Default Theme'),
                'Translations Theme'                    => Mage::helper('enterprise_support')->__('Translations Theme'),
                'Layouts Theme'                         => Mage::helper('enterprise_support')->__('Layouts Theme'),
                'Templates Theme'                       => Mage::helper('enterprise_support')->__('Templates Theme'),
                'Skin (Images / CSS)'                   => Mage::helper('enterprise_support')->__('Skin (Images / CSS)'),
                'File is too big'                       => Mage::helper('enterprise_support')->__('File is too big'),
                'File is not readable'                  => Mage::helper('enterprise_support')->__('File is not readable'),
                'Category Attributes'                   => Mage::helper('enterprise_support')->__('Category Attributes'),
                'Customer Address Attributes'           => Mage::helper('enterprise_support')->__('Customer Address Attributes'),
                'Core Cache Records'                    => Mage::helper('enterprise_support')->__('Core Cache Records'),
                'Core Cache Tags'                       => Mage::helper('enterprise_support')->__('Core Cache Tags'),
                'Product Attributes Flat Table Row Size'=> Mage::helper('enterprise_support')->__('Product Attributes Flat Table Row Size'),
                'Google Checkout Shipping - Merchant Calculated' => Mage::helper('enterprise_support')->__('Google Checkout Shipping - Merchant Calculated'),
                'Google Checkout Shipping - Carrier Calculated'  => Mage::helper('enterprise_support')->__('Google Checkout Shipping - Carrier Calculated'),
                'Google Checkout Shipping - Flat Rate'           => Mage::helper('enterprise_support')->__('Google Checkout Shipping - Flat Rate'),
                'Google Checkout Shipping - Digital Delivery'    => Mage::helper('enterprise_support')->__('Google Checkout Shipping - Digital Delivery'),
                'Payments Functionality Matrix is not available. ReflectionProperty::setAccessible is required for data collection, but it was implemented in PHP 5.3.0; your PHP version is lower.' => Mage::helper('enterprise_support')->__('Payments Functionality Matrix is not available. ReflectionProperty::setAccessible is required for data collection, but it was implemented in PHP 5.3.0; your PHP version is lower.'),
                '[VIEW]'                                => Mage::helper('enterprise_support')->__('[VIEW]'),
            );
        }

        if (!empty($this->_sysreportTranslatedValues[$value])) {
            $value = $this->_sysreportTranslatedValues[$value];
        } else {
            if ($this->_sysreportTranslatedReplaceValues === null) {
                $this->_sysreportTranslatedReplaceValues = array(
                    'Columns:'          => Mage::helper('enterprise_support')->__('Columns:'),
                    'Keys:'             => Mage::helper('enterprise_support')->__('Keys:'),
                    'Constraints:'      => Mage::helper('enterprise_support')->__('Constraints:'),
                    'Attribute Flags:'  => Mage::helper('enterprise_support')->__('Attribute Flags:'),
                    'Attribute Types:'  => Mage::helper('enterprise_support')->__('Attribute Types:'),
                    'Product Types:'    => Mage::helper('enterprise_support')->__('Product Types:'),
                    'TOTALS:'           => Mage::helper('enterprise_support')->__('TOTALS:'),
                    'Returns:'          => Mage::helper('enterprise_support')->__('Returns:'),
                    'Themes List'       => Mage::helper('enterprise_support')->__('Themes List'),
                    'Skins List'        => Mage::helper('enterprise_support')->__('Skins List'),
                    'bytes'             => Mage::helper('enterprise_support')->__('bytes'),
                );
            }
            $value = str_replace(
                array_keys($this->_sysreportTranslatedReplaceValues),
                array_values($this->_sysreportTranslatedReplaceValues),
                $value
            );
        }

        return $value;
    }

    /**
     * Calculate and retrieve time passed since now until specified date
     * Current time should be always greater or equal to specified date
     *
     * @param string $dateString
     *
     * @return string
     */
    public function getSinceTimeString($dateString)
    {
        $timeDiff = strtotime(now()) - strtotime($dateString);
        if ($timeDiff < 0) {
            return '';
        }
        if ($timeDiff == 0) {
            return Mage::helper('enterprise_support')->__('(now)');
        }

        $timeRanges = array(
            'minutes' => array('min' => 0, 'max' => 3540, 'div' => 60),
            'hours' => array('min' => 3540, 'max' => 82800, 'div' => 3600),
            'days' => array('min' => 82800, 'max' => 518400, 'div' => 86400),
            'weeks' => array('min' => 518400, 'max' => 1814400, 'div' => 604800),
            'months' => array('min' => 1814400, 'max' => 28512000, 'div' => 2592000),
            'years' => array('min' => 28512000, 'div' => 31536000),
        );

        $value = 0;
        $type = 'minutes';
        foreach ($timeRanges as $type => $timeData) {
            if ($timeDiff > $timeData['min']
                && ((isset($timeData['max']) && $timeDiff <= $timeData['max']) || !isset($timeData['max']))
            ) {
                $value = round($timeDiff / $timeData['div']);
                break;
            }
        }
        $value = $value == 0 ? 1 : $value;
        switch ($type) {
            case 'minutes':
                $value = Mage::helper('enterprise_support')->__('[%s minutes ago]', $value);
                break;
            case 'hours':
                $value = Mage::helper('enterprise_support')->__('[%s hours ago]', $value);
                break;
            case 'days':
                $value = Mage::helper('enterprise_support')->__('[%s days ago]', $value);
                break;
            case 'weeks':
                $value = Mage::helper('enterprise_support')->__('[%s weeks ago]', $value);
                break;
            case 'months':
                $value = Mage::helper('enterprise_support')->__('[%s months ago]', $value);
                break;
            case 'years': // break skipped intentionally
            default :
                $value = Mage::helper('enterprise_support')->__('[%s years ago]', $value);
                break;
        }

        return $value;
    }
}
