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
 * Index trigger helper
 *
 * @category   Enterprise
 * @package    Enterprise_Index
 */
class Enterprise_Index_Helper_Trigger extends Mage_Core_Helper_Abstract
{
    /**
     * Conditions array index constants
     */
    const INDEX_CONDITION = 'cond';
    const INDEX_PARAM     = 'param';

    /**
     * Connection interface
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Resource model
     *
     * @var Mage_Core_Model_Resource
     */
    protected $_resource;

    /**
     * Event model
     *
     * @var Enterprise_Mview_Model_Event
     */
    protected $_eventModel;

    /**
     * Constructor for class instance
     *
     * @param Enterprise_Mview_Model_Event $eventModel
     * @param Varien_Db_Adapter_Interface  $connection
     * @param Mage_Core_Model_Resource     $resource
     */
    public function __construct($eventModel = null, $connection = null, $resource = null)
    {
        if ($resource instanceof Mage_Core_Model_Resource) {
            $this->_resource = $resource;
        } else {
            $this->_resource = Mage::getSingleton('core/resource');
        }

        if ($eventModel instanceof Mage_Core_Model_Abstract) {
            $this->_eventModel = $eventModel;
        } else {
            $this->_eventModel = Mage::getModel('enterprise_mview/event');
        }

        if ($connection instanceof Varien_Db_Adapter_Interface) {
            $this->_connection = $connection;
        } else {
            $this->_connection = $this->_resource->getConnection(
                Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE
            );
        }
    }

    /**
     * Get system trigger sql code
     *
     * @param string $triggerEvent
     * @param string $eventTime
     * @param array  $entityEvents
     * @param string $table
     * @param string $conditionsLogic
     *
     * @throws InvalidArgumentException
     *
     * @return Magento_Db_Sql_Trigger
     */
    public function buildSystemTrigger($triggerEvent, $eventTime, array $entityEvents, $table,
                                       $conditionsLogic = Zend_Db_Select::SQL_OR)
    {
        /* @var $trigger Magento_Db_Sql_Trigger */
        $trigger = new Magento_Db_Sql_Trigger();
        $trigger->setTime($eventTime)
            ->setEvent($triggerEvent)
            ->setTarget($table);

        /** @var $event array('conditions' => array()) */
        foreach ($entityEvents as $eventName => $event) {
            $entityEventId = $this->_eventModel->load($eventName, 'name')->getId();
            $conditions    = (isset($event['conditions']) && is_array($event['conditions']))
                    ? $event['conditions'] : array();
            $trigger->setBodyPart(
                $eventName,
                $this->_wrapBodyWithCond($this->getTriggerBody($entityEventId), $conditions, $conditionsLogic)
            );
        }

        return $trigger;
    }

    /**
     * Get trigger action body for event
     *
     * @param int $entityEventId
     * @return string
     */
    public function getTriggerBody($entityEventId)
    {
        $select = $this->_connection->select()->reset()
            ->from(
                array(),
                array(
                    'status' => new Zend_Db_Expr('?')
                )
            )->joinInner(
                array(
                    'me' => $this->_resource->getTableName('enterprise_mview/metadata_event')
                ),
                new Zend_Db_Expr('mm.metadata_id = me.metadata_id'),
                array()
            )->where('mview_event_id = ?', $entityEventId);
        $update = $this->_connection->updateFromSelect(
            $select,
            array(
                'mm' =>$this->_resource->getTableName('enterprise_mview/metadata')
            )
        );
        $update = $this->_connection->quoteInto($update, Enterprise_Mview_Model_Metadata::STATUS_INVALID, null, 1);
        return $update . ';';
    }

    /**
     * Get events conditons SQL
     *
     * @param array $conditions
     * @param string $conditionsLogic
     *
     * @return string
     */
    public function getEventConditionsSql($conditions, $conditionsLogic)
    {
        $conditionStrings = array();
        foreach ($conditions as $condition) {
            $sql = $condition[self::INDEX_CONDITION];
            if (isset($condition[self::INDEX_PARAM])) {
                foreach ($condition[self::INDEX_PARAM] as $param) {
                    $sql = $this->_connection->quoteInto($sql, $param, null, 1);
                }
            }
            $conditionStrings[] = $sql;
        }
        return implode(' ' . $conditionsLogic . ' ', $conditionStrings);
    }

    /**
     * Wrap trigger body with conditions
     *
     * @param string $body
     * @param array $conditions
     * @param string $conditionsLogic
     * @return string
     */
    protected function _wrapBodyWithCond($body, $conditions = array(), $conditionsLogic =  Zend_Db_Select::SQL_OR)
    {
        if (empty($conditions)) {
            return $body;
        }
        return $this->_connection->getCaseSql(
            $this->getEventConditionsSql($conditions, $conditionsLogic),
            array('TRUE' => new Zend_Db_Expr('BEGIN ' . $body . ' END;')),
            new Zend_Db_Expr('BEGIN END;')
        ) .  " CASE;";
    }
}
