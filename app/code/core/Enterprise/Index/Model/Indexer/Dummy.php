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
 * @package     Enterprise_Index
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Indexer dummy
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Indexer_Dummy extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * @var int
     */
    protected $_sortOrder = 0;

    /**
     * (Dummy) Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return '';
    }

    /**
     * (Dummy) Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        return;
    }

    /**
     * (Dummy) Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        return;
    }

    /**
     * (Dummy) Register indexer event
     *
     * @param Mage_Index_Model_Event $event
     *
     * @return Enterprise_Index_Model_Indexer_Dummy|Mage_Index_Model_Indexer_Abstract
     */
    public function register(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    /**
     * (Dummy) Process event
     *
     * @param   Mage_Index_Model_Event $event
     * @return  Mage_Index_Model_Indexer_Abstract
     */
    public function processEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    /**
     * (Dummy) Check if event can be matched by process
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        return false;
    }

    /**
     * (Dummy) Check if indexer matched specific entity and action type
     *
     * @param   string $entity
     * @param   string $type
     * @return  bool
     */
    public function matchEntityAndType($entity, $type)
    {
        return false;
    }

    /**
     * (Dummy) Rebuild all index data
     */
    public function reindexAll()
    {
        return;
    }

    /**
     * (Dummy) Try dynamicly detect and call event hanler from resource model.
     * Handler name will be generated from event entity and type code
     *
     * @param   Mage_Index_Model_Event $event
     * @return  Mage_Index_Model_Indexer_Abstract
     */
    public function callEventHandler(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    /**
     * (Dummy) Set whether table changes are allowed
     *
     * @deprecated after 1.6.1.0
     * @param bool $value
     * @return Mage_Index_Model_Indexer_Abstract
     */
    public function setAllowTableChanges($value = true)
    {
        return $this;
    }

    /**
     * (Dummy) Disable resource table keys
     *
     * @return Mage_Index_Model_Indexer_Abstract
     */
    public function disableKeys()
    {
        return $this;
    }

    /**
     * (Dummy) Enable resource table keys
     *
     * @return Mage_Index_Model_Indexer_Abstract
     */
    public function enableKeys()
    {
        return $this;
    }

    /**
     * Magic call dummy method
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|void
     */
    public function __call($name, $arguments)
    {
        return;
    }

    /**
     * Set visibility of indexer
     *
     * @param string $visibility
     * @return Enterprise_Index_Model_Indexer_Dummy
     */
    public function setVisibility($visibility)
    {
        $this->_isVisible = (bool)$visibility;
        return $this;
    }

    /**
     * Set order for sort
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder = 0)
    {
        $this->_sortOrder = (int)$sortOrder;
        return $this;
    }

    /**
     * Get order for sort
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_sortOrder;
    }
}
