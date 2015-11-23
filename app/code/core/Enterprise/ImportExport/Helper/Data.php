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
 * @package     Enterprise_ImportExport
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * ImportExport data helper
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_ImportExport_Helper_Data extends Mage_ImportExport_Helper_Data
{
    /**
     * Get operation header text
     *
     * @param string $type   operation type
     * @param string $action
     * @return string
     */
    public function getOperationHeaderText($type, $action = 'new')
    {
        $title = '';
        switch ($type) {
            case 'import':
                if ($action == 'edit') {
                    $title = $this->__('Edit Scheduled Import');
                } else {
                    $title = $this->__('New Scheduled Import');
                }
                break;
            case 'export':
                if ($action == 'edit') {
                    $title = $this->__('Edit Scheduled Export');
                } else {
                    $title = $this->__('New Scheduled Export');
                }
                break;
        }

        return $title;
    }

    /**
     * Get seccess operation save message
     *
     * @param string $type   operation type
     * @return string
     */
    public function getSuccessSaveMessage($type)
    {
        switch ($type) {
            case 'import':
                $message = $this->__('The scheduled import has been saved.');
                break;
            case 'export':
                $message = $this->__('The scheduled export has been saved.');
                break;
        }

        return $message;
    }

    /**
     * Get seccess operation delete message
     *
     * @param string $type   operation type
     * @return string
     */
    public function getSuccessDeleteMessage($type)
    {
        switch ($type) {
            case 'import':
                $message = $this->__('The scheduled import has been deleted.');
                break;
            case 'export':
                $message = $this->__('The scheduled export has been deleted.');
                break;
        }

        return $message;
    }

    /**
     * Get confirmation message
     *
     * @param string $type   operation type
     * @return string
     */
    public function getConfirmationDeleteMessage($type)
    {
        switch ($type) {
            case 'import':
                $message = $this->__('Are you sure you want to delete this scheduled import?');
                break;
            case 'export':
                $message = $this->__('Are you sure you want to delete this scheduled export?');
                break;
        }

        return $message;
    }

    /**
     * Get notice operation message
     *
     * @param string $type   operation type
     * @return string
     */
    public function getNoticeMessage($type)
    {
        $message = '';
        if ($type == 'import') {
            $maxUploadSize = $this->getMaxUploadSize();
            $message = $this->__('Total size of the file must not exceed %s', $maxUploadSize);
        }
        return $message;
    }
}
