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
 * General Logging container
 */
class Enterprise_Logging_Block_Adminhtml_Container extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Curent event data storage
     *
     * @deprecated after 1.6.0.0
     * @var object
     */
    protected $_eventData = null;

    /**
     * Remove add button
     * Set block group and controller
     *
     */
    public function __construct()
    {
        $action = Mage::app()->getRequest()->getActionName();
        $this->_blockGroup = 'enterprise_logging';
        $this->_controller = 'adminhtml_' . $action;

        parent::__construct();
        $this->_removeButton('add');
    }

    /**
     * Header text getter
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('enterprise_logging')->__($this->getData('header_text'));
    }

    /**
     * Get current event data
     *
     * @deprecated after 1.6.0.0
     * @return object Enterprise_Logging_Model_Event
     */
    public function getEventData()
    {
        if (!$this->_eventData) {
            $this->_eventData = Mage::registry('current_event');
        }
        return $this->_eventData;
    }

    /**
     * Convert x_forwarded_ip to string
     *
     * @deprecated after 1.6.0.0
     * @return string
     */
    public function getEventXForwardedIp()
    {
        /**
         * The output of the "inet_ntop" function was disabled to prevent an error throwing
         * in case when the database value is not an ipv6 or an ipv4 binary representation (ex. NULL).
         */
        return @inet_ntop($this->getEventData()->getXForwardedIp());
    }

    /**
     * Convert ip to string
     *
     * @deprecated after 1.6.0.0
     * @return string
     */
    public function getEventIp()
    {
        /**
         * The output of the "inet_ntop" function was disabled to prevent an error throwing
         * in case when the database value is not an ipv6 or an ipv4 binary representation (ex. NULL).
         */
        return @inet_ntop($this->getEventData()->getIp());
    }

    /**
     * Replace /n => <br /> in event error_message
     *
     * @deprecated after 1.6.0.0
     * @return string
     */
    public function getEventError()
    {
        return nl2br($this->getEventData()->getErrorMessage());
    }
}
