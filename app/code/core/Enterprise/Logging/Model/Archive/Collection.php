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
 * Archive files collection
 */
class Enterprise_Logging_Model_Archive_Collection extends Varien_Data_Collection_Filesystem
{
    /**
     * Filenames regex filter
     *
     * @var string
     */
    protected $_allowedFilesMask = '/^[a-z0-9\.\-\_]+\.csv$/i';

    /**
     * Set target dir for scanning
     */
    public function __construct()
    {
        parent::__construct();
        $basePath = Mage::getModel('enterprise_logging/archive')->getBasePath();
        $file = new Varien_Io_File();
        $file->setAllowCreateFolders(true)->createDestinationDir($basePath);
        $this->addTargetDir($basePath);
    }

    /**
     * Row generator
     * Add 'time' column as Zend_Date object
     * Add 'timestamp' column as unix timestamp - used in date filter
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $row = parent::_generateRow($filename);
        $date = new Zend_Date(str_replace('.csv', '', $row['basename']), 'yyyyMMddHH', Mage::app()->getLocale()->getLocaleCode());
        $row['time'] = $date;
        /**
         * Used in date filter, becouse $date contains hours
         */
        $dateWithoutHours = new Zend_Date(str_replace('.csv', '', $row['basename']), 'yyyyMMdd', Mage::app()->getLocale()->getLocaleCode());
        $row['timestamp'] = $dateWithoutHours->toString('yyyy-MM-dd');
        return $row;
    }

    /**
     * Custom callback method for 'moreq' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackIsMoreThan($field, $filterValue, $row)
    {
        $rowValue = $row[$field];
        if ($field == 'time') {
            $rowValue    = $row['timestamp'];
        }
        return $rowValue > $filterValue;
    }

    /**
     * Custom callback method for 'lteq' fancy filter
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filterCallbackIsLessThan($field, $filterValue, $row)
    {
        $rowValue = $row[$field];
        if ($field == 'time') {
            $rowValue    = $row['timestamp'];
        }
        return $rowValue < $filterValue;
    }
}
