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
 * @package     Enterprise_Checkout
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Import data from file
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 */
class Enterprise_Checkout_Model_Import extends Varien_Object
{
    /**
     * Form field name
     */
    const FIELD_NAME_SOURCE_FILE = 'sku_file';

    /**
     * Uploaded file name
     *
     * @var string
     */
    protected $_uploadedFile = '';

    /**
     * Allowed file name extensions to upload
     *
     * @var array
     */
    protected $_allowedExtensions = array(
        'csv'
    );

    public function __construct()
    {
        // Initialize shutdown function
        register_shutdown_function(array($this, 'destruct'));
        parent::__construct();
    }

    /**
     * Removes uploaded file on shutdown
     */
    public function destruct()
    {
        if (!empty($this->_uploadedFile)) {
            unlink($this->_uploadedFile);
        }
    }

    /**
     * Upload file
     *
     * @return void
     */
    public function uploadFile()
    {
        /** @var $uploader Mage_Core_Model_File_Uploader */
        $uploader  = Mage::getModel('core/file_uploader', self::FIELD_NAME_SOURCE_FILE);
        $uploader->setAllowedExtensions($this->_allowedExtensions);
        $uploader->skipDbProcessing(true);
        if (!$uploader->checkAllowedExtension($uploader->getFileExtension())) {
            Mage::throwException($this->_getFileTypeMessageText());
        }

        try {
            $result = $uploader->save($this->_getWorkingDir());
            $this->_uploadedFile = $result['path'] . $result['file'];
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('enterprise_checkout')->getFileGeneralErrorText());
        }
    }

    /**
     * Get rows from file
     *
     * @return array
     */
    public function getRows()
    {
        $extension = pathinfo($this->_uploadedFile, PATHINFO_EXTENSION);
        $method = $this->_getMethodByExtension(strtolower($extension));
        if (!empty($method) && method_exists($this, $method)) {
            return $this->$method();
        }

        Mage::throwException($this->_getFileTypeMessageText());
    }

    /**
     * Get rows from CSV file
     *
     * @return array
     */
    public function getDataFromCsv()
    {
        if (!$this->_uploadedFile || !file_exists($this->_uploadedFile)) {
            Mage::throwException(Mage::helper('enterprise_checkout')->getFileGeneralErrorText());
        }

        $csvData = array();

        try {
            $fileHandler = fopen($this->_uploadedFile, 'r');
            if ($fileHandler) {
                $colNames = fgetcsv($fileHandler);

                foreach ($colNames as &$colName) {
                    $colName = trim($colName);
                }

                $requiredColumns = array('sku', 'qty');
                $requiredColumnsPositions = array();

                foreach ($requiredColumns as $columnName) {
                    $found = array_search($columnName, $colNames);
                    if (false !== $found) {
                        $requiredColumnsPositions[] = $found;
                    } else {
                        Mage::throwException(Mage::helper('enterprise_checkout')->getSkuEmptyDataMessageText());
                    }
                }

                while (($currentRow = fgetcsv($fileHandler)) !== false) {
                    $csvDataRow = array('qty' => '');
                    foreach ($requiredColumnsPositions as $index) {
                        if (isset($currentRow[$index])) {
                            $csvDataRow[$colNames[$index]] = trim($currentRow[$index]);
                        }
                    }
                    if (isset($csvDataRow['sku']) && $csvDataRow['sku'] !== '') {
                        $csvData[] = $csvDataRow;
                    }
                }
                fclose($fileHandler);
            }
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('enterprise_checkout')->__('File is corrupt.'));
        }
        return $csvData;
    }

    /**
     * Import SKU working directory
     *
     * @return string
     */
    protected function _getWorkingDir()
    {
        return Mage::getBaseDir('var') . DS . 'import_sku' . DS;
    }

    /**
     * Get Method to load data by file extension
     *
     * @param string $extension
     * @return bool|string
     */
    protected function _getMethodByExtension($extension)
    {
        foreach($this->_allowedExtensions as $allowedExtension) {
            if ($allowedExtension == $extension) {
                return 'getDataFrom' . ucfirst($allowedExtension);
            }
        }

        Mage::throwException($this->_getFileTypeMessageText());
        return false;
    }

    /**
     * Get message text of wrong file type error
     *
     * @return string
     */
    protected function _getFileTypeMessageText()
    {
        return Mage::helper('enterprise_checkout')->__('Only .csv file format is supported.');
    }
}
