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
 * Catalog Event model
 *
 * @method Enterprise_CatalogEvent_Model_Resource_Event _getResource()
 * @method Enterprise_CatalogEvent_Model_Resource_Event getResource()
 * @method int getCategoryId()
 * @method Enterprise_CatalogEvent_Model_Event setCategoryId(int $value)
 * @method string getDateStart()
 * @method Enterprise_CatalogEvent_Model_Event setDateStart(string $value)
 * @method string getDateEnd()
 * @method Enterprise_CatalogEvent_Model_Event setDateEnd(string $value)
 * @method int getDisplayState()
 * @method int getSortOrder()
 * @method Enterprise_CatalogEvent_Model_Event setSortOrder(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogEvent
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogEvent_Model_Event extends Mage_Core_Model_Abstract
{
    const DISPLAY_CATEGORY_PAGE = 1;
    const DISPLAY_PRODUCT_PAGE  = 2;

    const STATUS_UPCOMING       = 'upcoming';
    const STATUS_OPEN           = 'open';
    const STATUS_CLOSED         = 'closed';

    const CACHE_TAG             = 'catalog_event';

    /**
     * Path to time zone in configuration
     *
     * @deprecated after 1.3.2.3
     */
    const XML_PATH_DEFAULT_TIMEZONE = 'general/locale/timezone';

    const IMAGE_PATH = 'enterprise/catalogevent';

    protected $_store = null;

    /**
     * Model cache tag for clear cache in after save and after delete
     */
    protected $_cacheTag        = self::CACHE_TAG;

    /**
     * Is model deleteable
     *
     * @var boolean
     */
    protected $_isDeleteable = true;

    /**
     * Is model readonly
     *
     * @var boolean
     */
    protected $_isReadonly = false;

    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enterprise_catalogevent/event');
    }

    /**
     * Get cahce tags associated with object id.
     * Added category id tags support
     *
     * @return array
     */
    public function getCacheIdTags()
    {
        $tags = parent::getCacheIdTags();
        if ($this->getCategoryId()) {
            $tags[] = Mage_Catalog_Model_Category::CACHE_TAG . '_' . $this->getCategoryId();
        }
        return $tags;
    }

    /**
     * Apply event status
     *
     * @return Enterprise_CatalogEvent_Model_Event
     */
    protected function _afterLoad()
    {
        $this->_initDisplayStateArray();
        parent::_afterLoad();
        $this->getStatus();
        return $this;
    }

    /**
     * Initialize display state as array
     *
     * @return Enterprise_CatalogEvent_Model_Event
     */
    protected function _initDisplayStateArray()
    {
        $state = array();
        if ($this->canDisplayCategoryPage()) {
            $state[] = self::DISPLAY_CATEGORY_PAGE;
        }
        if ($this->canDisplayProductPage()) {
            $state[] = self::DISPLAY_PRODUCT_PAGE;
        }
        $this->setDisplayStateArray($state);
        return $this;
    }

    /**
     * Set store id
     *
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function setStoreId($storeId = null)
    {
        $this->_store = Mage::app()->getStore($storeId);
        return $this;
    }

    /**
     * Retrieve store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->_store === null) {
            $this->setStoreId();
        }

        return $this->_store;
    }

    /**
     * Set event image
     *
     * @param string|null|Mage_Core_Model_File_Uploader $value
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function setImage($value)
    {
        //in the current version should be used instance of Mage_Core_Model_File_Uploader
        if ($value instanceof Varien_File_Uploader) {
            $value->save(Mage::getBaseDir('media') . DS
                         . strtr(self::IMAGE_PATH, '/', DS));

            $value = $value->getUploadedFileName();
        }

        $this->setData('image', $value);
        return $this;
    }

    /**
     * Retrieve image url
     *
     * @return string|boolean
     */
    public function getImageUrl()
    {
        if ($this->getImage()) {
            return Mage::getBaseUrl('media') . '/'
                   . self::IMAGE_PATH . '/' . $this->getImage();
        }

        return false;
    }

    /**
     * Retrieve store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Set display state of catalog event
     *
     * @param int|array $state
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function setDisplayState($state)
    {
        if (is_array($state)) {
            $value = 0;
            foreach ($state as $_state) {
                $value ^= $_state;
            }
            $this->setData('display_state', $value);
        } else {
            $this->setData('display_state', $state);
        }
        return $this;
    }

    /**
     * Check display state for page type
     *
     * @param int $state
     * @return boolean
     */
    public function canDisplay($state)
    {
        return ((int) $this->getDisplayState() & $state) == $state;
    }

    /**
     * Check display state for product view page
     *
     * @return boolean
     */
    public function canDisplayProductPage()
    {
        return $this->canDisplay(self::DISPLAY_PRODUCT_PAGE);
    }

    /**
     * Check display state for category view page
     *
     * @return boolean
     */
    public function canDisplayCategoryPage()
    {
        return $this->canDisplay(self::DISPLAY_CATEGORY_PAGE);
    }

    /**
     * Apply event status by date
     *
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function applyStatusByDates()
    {
        if ($this->getDateStart() && $this->getDateEnd()) {
            $timeStart = $this->_getResource()->mktime($this->getDateStart()); // Date already in gmt, no conversion
            $timeEnd = $this->_getResource()->mktime($this->getDateEnd()); // Date already in gmt, no conversion
            $timeNow = gmdate('U');
            if ($timeStart <= $timeNow && $timeEnd >= $timeNow) {
                $this->setStatus(self::STATUS_OPEN);
            } elseif ($timeNow > $timeEnd) {
                $this->setStatus(self::STATUS_CLOSED);
            } else {
                $this->setStatus(self::STATUS_UPCOMING);
            }
        }
        return $this;
    }

    /**
     * Retrieve category ids with events
     *
     * @param int|string|Mage_Core_Model_Store $storeId
     * @return array
     */
    public function getCategoryIdsWithEvent($storeId = null)
    {
        return $this->_getResource()->getCategoryIdsWithEvent($storeId);
    }

    /**
     * Before save. Validation of data, and applying status, if needed.
     *
     * @return Enterprise_CatalogEvent_Model_Event
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $dateChanged = false;
        $fieldTitles = array(
            'date_start' => Mage::helper('enterprise_catalogevent')->__('Start Date') ,
            'date_end' => Mage::helper('enterprise_catalogevent')->__('End Date')
        );
        foreach (array('date_start' , 'date_end') as $dateType) {
            $date = $this->getData($dateType);
            if (empty($date)) { // Date fields is required.
                Mage::throwException(
                    Mage::helper('enterprise_catalogevent')->__('%s is required.', $fieldTitles[$dateType])
                );
            }
            if ($date != $this->getOrigData($dateType)) {
                $dateChanged = true;
            }
        }
        if ($dateChanged) {
            $this->applyStatusByDates();
        }

        return $this;
    }

    /**
     * Validates data for event
     * @returns boolean|array - returns true if validation passed successfully. Array with error
     * description otherwise
     */
    public function validate()
    {
        $dateStartUnixTime = strtotime($this->getData('date_start'));
        $dateEndUnixTime   = strtotime($this->getData('date_end'));
        $dateIsOk = $dateEndUnixTime > $dateStartUnixTime;
        if ($dateIsOk) {
            return true;
        }
        else {
            return array(Mage::helper('enterprise_catalogevent')->__('End date should be greater than start date.'));
        }
    }

    /**
     * Converts given date to internal date format in UTC
     *
     * @deprecated after 1.3.2.3
     * @param  string $dateTime
     * @param  string $format
     * @return string
     */
    protected function _convertDateTime($dateTime, $format)
    {
        $date = new Zend_Date(Mage::app()->getLocale()->getLocale());
        $date->setTimezone(Mage::app()->getStore()->getConfig(self::XML_PATH_DEFAULT_TIMEZONE));
        $format = Mage::app()->getLocale()->getDateTimeFormat($format);
        $date->set($dateTime, $format);
        $date->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }


    /**
     * Checks if object can be deleted
     *
     * @return boolean
     */
    public function isDeleteable()
    {
        return $this->_isDeleteable;
    }

    /**
     * Sets flag for object if it can be deleted or not
     *
     * @param boolean $value
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function setIsDeleteable($value)
    {
        $this->_isDeleteable = (boolean) $value;
        return $this;
    }

    /**
     * Checks model is read only
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->_isReadonly;
    }

    /**
     * Set is read only flag
     *
     * @param boolean $value
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function setIsReadonly($value)
    {
        $this->_isReadonly = (boolean) $value;
        return $this;
    }

    /**
     * @deprecated after 1.3.2.2
     */
    public function updateStatus()
    {
        $originalStatus = $this->getStatus();
        if ($originalStatus == self::STATUS_OPEN || $originalStatus == self::STATUS_UPCOMING) {
            $this->applyStatusByDates();
            if ($this->getStatus() != $originalStatus) {
                $this->save();
            }
        }
    }

    /**
     * Get status column value
     * Set status column if it wasn't set
     *
     * @return string
     */
    public function getStatus()
    {
        if (!$this->hasData('status')) {
            $this->applyStatusByDates();
        }
        return $this->_getData('status');
    }

    /**
     * Converts passed start time value in sotre's
     * time zone to UTC time zone and sets it to object.
     *
     * @param string $value date time in store's time zone
     * @param mixed $store
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function setStoreDateStart($value, $store = null)
    {
        $date = Mage::app()->getLocale()->utcDate($store, $value, true, Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->setData('date_start', $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        return $this;
    }

    /**
     * Converts passed end time value in sotre's
     * time zone to UTC time zone and sets it to object.
     *
     * @param string $value date time in store's time zone
     * @param mixed $store
     * @return Enterprise_CatalogEvent_Model_Event
     */
    public function setStoreDateEnd($value, $store = null)
    {
        $date = Mage::app()->getLocale()->utcDate($store, $value, true, Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->setData('date_end', $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        return $this;
    }

    /**
     * Gets start time from object, converts it from UTC time zone
     * to store's time zone. Result is formatted by internal format
     * and in time zone of current store or passed through parameter.
     *
     * @param mixed $store
     * @return string
     */
    public function getStoreDateStart($store = null)
    {
        if ($this->getData('date_start')) {
            $value = $this->getResource()->mktime($this->getData('date_start'));
            if (!$value) {
                return null;
            }
            $date = Mage::app()->getLocale()->storeDate($store, $value, true);
            return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        return $this->getData('date_start');
    }

    /**
     * Gets end time from object, converts it from UTC time zone
     * to store's time zone. Result is formatted by internal format
     * and in time zone of current store or passed through parameter.
     *
     * @param mixed $store
     * @return string
     */
    public function getStoreDateEnd($store = null)
    {
        if ($this->getData('date_end')) {
            $value = $this->getResource()->mktime($this->getData('date_end'));
            if (!$value) {
                return null;
            }
            $date = Mage::app()->getLocale()->storeDate($store, $value, true);
            return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        return $this->getData('date_end');
    }
}
