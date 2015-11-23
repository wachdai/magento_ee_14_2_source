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
 * Log grid container
 */
class Enterprise_Logging_Block_Adminhtml_Details extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Store curent event
     *
     * @var Enterprise_Logging_Model_Event
     */
    protected $_currentEevent = null;

    /**
     * Store current event user
     *
     * @var Mage_Admin_Model_User
     */
    protected $_eventUser = null;

    /**
     * Add back button
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_addButton('back', array(
            'label'   => Mage::helper('enterprise_logging')->__('Back'),
            'onclick' => "setLocation('" . Mage::getSingleton('adminhtml/url')->getUrl('*/*/'). "')",
            'class'   => 'back'
        ));
    }

    /**
     * Header text getter
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getCurrentEvent()) {
            return Mage::helper('enterprise_logging')->__('Log Entry #%d', $this->getCurrentEvent()->getId());
        }
        return Mage::helper('enterprise_logging')->__('Log Entry Details');
    }

    /**
     * Get current event
     *
     * @return Enterprise_Logging_Model_Event|null
     */
    public function getCurrentEvent()
    {
        if (null === $this->_currentEevent) {
            $this->_currentEevent = Mage::registry('current_event');
        }
        return $this->_currentEevent;
    }

    /**
     * Convert x_forwarded_ip to string
     *
     * @return string|bool
     */
    public function getEventXForwardedIp()
    {
        if ($this->getCurrentEvent()) {
            /**
             * The output of the "inet_ntop" function was disabled to prevent an error throwing
             * in case when the database value is not an ipv6 or an ipv4 binary representation (ex. NULL).
             */
            $xForwarderFor = @inet_ntop($this->getCurrentEvent()->getXForwardedIp());
            if ($xForwarderFor && $xForwarderFor != '0.0.0.0') {
                return $xForwarderFor;
            }
        }
        return false;
    }

    /**
     * Convert ip to string
     *
     * @return string|bool
     */
    public function getEventIp()
    {
        if ($this->getCurrentEvent()) {
            /**
             * The output of the "inet_ntop" function was disabled to prevent an error throwing
             * in case when the database value is not an ipv6 or an ipv4 binary representation (ex. NULL).
             */
            return @inet_ntop($this->getCurrentEvent()->getIp());
        }
        return false;
    }

    /**
     * Replace /n => <br /> in event error_message
     *
     * @return string|bool
     */
    public function getEventError()
    {
        if ($this->getCurrentEvent()) {
            return nl2br($this->getCurrentEvent()->getErrorMessage());
        }
        return false;
    }

    /**
     * Get current event user
     *
     * @return Mage_Admin_Model_User|null
     */
    public function getEventUser()
    {
        if (null === $this->_eventUser) {
            $this->_eventUser = Mage::getModel('admin/user')->load($this->getUserId());
        }
        return $this->_eventUser;
    }

    /**
     * Unserialize and retrive event info
     *
     * @return string
     */
    public function getEventInfo()
    {
        $info = null;
        $data = $this->getCurrentEvent()->getInfo();
        try {
            $info = unserialize($data);
        } catch (Exception $e) {
            $info = $data;
        }
        return $info;
    }
}
