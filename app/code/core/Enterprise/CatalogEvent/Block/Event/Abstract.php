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
 * @package     Enterprise_CatalogEvent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Catalog Event Abstract event block
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogEvent
 */

abstract class Enterprise_CatalogEvent_Block_Event_Abstract extends Mage_Core_Block_Template
{
    /**
     * Event statuses titles
     *
     * @var array
     */
    protected $_statuses;

    protected function _construct()
    {
        parent::_construct();
        $this->_statuses = array(
            Enterprise_CatalogEvent_Model_Event::STATUS_UPCOMING => $this->helper('enterprise_catalogevent')->__('Coming Soon'),
            Enterprise_CatalogEvent_Model_Event::STATUS_OPEN     => $this->helper('enterprise_catalogevent')->__('Sale Ends In'),
            Enterprise_CatalogEvent_Model_Event::STATUS_CLOSED   => $this->helper('enterprise_catalogevent')->__('Closed'),
        );
    }

    /**
     * Return catalog event status text
     *
     * @param Enterprise_CatalogEvent_Model_Event $event
     * @return string
     */
    public function getStatusText($event)
    {
        if (isset($this->_statuses[$event->getStatus()])) {
            return $this->_statuses[$event->getStatus()];
        }

        return '';
    }

    /**
     * Return event formatted time
     *
     * @param string $type (start, end)
     * @param Enterprise_CatalogEvent_Model_Event $event
     * @param string $format
     * @return string
     */
    public function getEventTime($type, $event, $format = null)
    {
        if ($format === null) {
            $format = $this->_getLocale()->getTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        }

        return $this->_getEventDate($type, $event, $format);
    }

    /**
     * Return event formatted date
     *
     * @param string $type (start, end)
     * @param Enterprise_CatalogEvent_Model_Event $event
     * @param string $format
     * @return string
     */
    public function getEventDate($type, $event, $format = null)
    {
        if ($format === null) {
            $format = $this->_getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        }

        return $this->_getEventDate($type, $event, $format);

    }

    /**
     * Return event formatted datetime
     *
     * @param string $type (start, end)
     * @param Enterprise_CatalogEvent_Model_Event $event
     * @param string $format
     * @return string
     */
    public function getEventDateTime($type, $event)
    {
        return $this->getEventDate($type, $event) . ' ' . $this->getEventDate($type, $event);
    }

    /**
     * Return event date by in store timezone, with specified format
     *
     * @param string $type (start, end)
     * @param Enterprise_CatalogEvent_Model_Event $event
     * @param string $format
     * @return string
     */
    protected function _getEventDate($type, $event, $format)
    {
        $date = new Zend_Date($this->_getLocale()->getLocale());
        // changing timezone to UTC
        $date->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);

        $dateString = $event->getData('date_' . $type);
        $date->set($dateString, Varien_Date::DATETIME_INTERNAL_FORMAT);

        if (($timezone = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE))) {
            // changing timezone to default store timezone
            $date->setTimezone($timezone);
        }
        return $date->toString($format);
    }

    /**
     * Return event time to close in seconds
     *
     * @param Enterprise_CatalogEvent_Model_Event $event
     * @return int
     */
    public function getSecondsToClose($event)
    {
        $endTime = strtotime($event->getDateEnd());
        $currentTime = gmdate('U');

        return $endTime - $currentTime;
    }

    /**
     * Retrieve current locale
     *
     * @return Mage_Core_Model_Locale
     */
    protected function _getLocale()
    {
        return Mage::app()->getLocale();
    }

    /**
     * Check availability to display event block
     *
     * @return boolean
     */
    abstract public function canDisplay();

}
