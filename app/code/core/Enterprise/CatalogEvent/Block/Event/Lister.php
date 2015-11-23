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
 * Catalog Event homepage block
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogEvent
 */
class Enterprise_CatalogEvent_Block_Event_Lister extends Enterprise_CatalogEvent_Block_Event_Abstract
{
    /**
     * Events list
     *
     * @var array
     */
    protected $_events = null;

    /**
     * Retrieve html id
     *
     * @return string
     */
    public function getHtmlId()
    {
        if (!$this->hasData('html_id')) {
            $this->setData('html_id', 'id_' . md5(uniqid('catalogevent', true)));
        }

        return $this->getData('html_id');
    }

    /**
     * Check whether the block can be displayed
     *
     * @return bool
     */
    public function canDisplay()
    {
        return Mage::helper('enterprise_catalogevent')->isEnabled()
            && Mage::getStoreConfigFlag('catalog/enterprise_catalogevent/lister_output')
            && (count($this->getEvents()) > 0);
    }

    /**
     * Retrieve categories with events
     *
     * @return array
     */
    public function getEvents()
    {
        if ($this->_events === null) {
            $this->_events = array();
            $categories = $this->helper('catalog/category')->getStoreCategories('position', true, false);
            if (($categories instanceof Mage_Eav_Model_Entity_Collection_Abstract) ||
                ($categories instanceof Mage_Core_Model_Resource_Db_Collection_Abstract)) {
                $allIds = $categories->getAllIds();
            } else {
                $allIds = array();
            }

            if (!empty($allIds)) {
                $eventCollection = Mage::getModel('enterprise_catalogevent/event')
                    ->getCollection();
                $eventCollection->addFieldToFilter('category_id', array('in' => $allIds))
                    ->addVisibilityFilter()
                    ->addImageData()
                    ->addSortByStatus()
                ;

                $categories->addIdFilter(
                    $eventCollection->getColumnValues('category_id')
                );

                foreach ($categories as $category) {
                    $event = $eventCollection->getItemByColumnValue('category_id', $category->getId());
                    if ($category->getIsActive()) {
                        $event->setCategory($category);
                    } else {
                        $eventCollection->removeItemByKey($event->getId());
                    }
                }

                foreach ($eventCollection as $event) {
                    $this->_events[] = $event;
                }


            }
        }

        return $this->_events;
    }

    /**
     * Retreive category url
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        return $this->helper('catalog/category')->getCategoryUrl($category);
    }

    /**
     * Retrieve catalog category image url
     *
     * @param Enterprise_CatalogEvent_Model_Event $event
     * @return string
     */
    public function getEventImageUrl($event)
    {
        return $this->helper('enterprise_catalogevent')->getEventImageUrl($event);
    }

    /**
     * Get items number to show per page
     *
     * @return int
     */
    public function getPageSize()
    {
        if ($this->hasData('limit') && is_numeric($this->getData('limit'))) {
            $pageSize = (int) $this->_getData('limit');
        }
        else {
            $pageSize = (int)Mage::getStoreConfig('catalog/enterprise_catalogevent/lister_widget_limit');
        }
        return max($pageSize, 1);
    }

    /**
     * Get items number to scroll
     *
     * @return int
     */
    public function getScrollSize()
    {
        if ($this->hasData('scroll') && is_numeric($this->getData('scroll'))) {
            $scrollSize = (int) $this->_getData('scroll');
        }
        else {
            $scrollSize = (int)Mage::getStoreConfig('catalog/enterprise_catalogevent/lister_widget_scroll');
        }
        return  min(max($scrollSize, 1), $this->getPageSize());
    }

    /**
     * Output content, if allowed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->canDisplay()) {
            return '';
        }
        return parent::_toHtml();
    }
}
