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
 * @package     Enterprise_License
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * License Adminhtml Block
 *
 * @category   Enterprise
 * @package    Enterprise_License
 */

class Enterprise_License_Block_Adminhtml_Checker extends Mage_Core_Block_Template
{
    /**
     * Number of days until the expiration of license.
     *
     * @var int
     */
    protected $_daysLeftBeforeExpired;

    /**
     * Сounts the number of days remaining until the expiration of license.
     * 
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $data = Mage::getSingleton('admin/session')->getDaysLeftBeforeExpired();
        $this->_daysLeftBeforeExpired = $data['daysLeftBeforeExpired'];
    }

    /**
     * Decides it's time to show warning or not.
     * 
     * @return bool
     */
    public function shouldDispalyNotification()
    {
        $enterprise_license=Mage::helper('enterprise_license');
        if($enterprise_license->isIoncubeLoaded() && $enterprise_license->isIoncubeEncoded()) {
            return ($this->_daysLeftBeforeExpired < 31);
        } else {
            return false;
        }
    }
    

    /**
     * Getter: return counts of days remaining until the expiration of license.
     * 
     * @return int
     */
    public function getDaysLeftBeforeExpired()
    {
        return $this->_daysLeftBeforeExpired;
    }

    /**
     * Returns the text to be displayed in the message.
     * 
     * @return string
     */
    public function getMessage()
    {
        $message = "";

        $days = $this->getDaysLeftBeforeExpired();

        if($days < 0) {
            $message = Mage::helper('enterprise_license')->__('Your Magento Enteprise Edition license expired. Please contact <a href="mailto:sales@varien.com">sales@varien.com</a> to renew the license.');
        } elseif(0 == $days) {
            $message = Mage::helper('enterprise_license')->__('Your Magento Enteprise Edition expires today. Please contact <a href="mailto:sales@varien.com">sales@varien.com</a> to renew the license.');
        } elseif($days < 31) {
            $message = Mage::helper('enterprise_license')->__('Your Magento Enteprise Edition will expire in %d days. Please contact <a href="mailto:sales@varien.com">sales@varien.com</a> to renew the license.', $days);
        }

        return $message;
    }
}
