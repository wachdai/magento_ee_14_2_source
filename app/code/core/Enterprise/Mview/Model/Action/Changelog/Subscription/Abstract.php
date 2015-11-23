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
 * @package     Enterprise_Mview
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Parent class for Changelog subscription
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Mview_Model_Action_Changelog_Subscription_Abstract
    implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Connection instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Metadata instance
     *
     * @var Enterprise_Mview_Model_Metadata
     */
    protected $_metadata;

    /**
     * Factory instance
     *
     * @var Enterprise_Mview_Model_Factory
     */
    protected $_factory;

    /**
     * Target table name
     *
     * @var string
     */
    protected $_targetTable;

    /**
     * Constructor
     * Arguments:
     *  connection - Varien_Db_Adapter_Interface;
     *  metadata - Enterprise_Mview_Model_Metadata object;
     *  factory - Enterprise_Mview_Model_Factory;
     *  target_table - string;
     *
     * @param array $args
     * @throws InvalidArgumentException
     */
    public function __construct(array $args)
    {
        $this->_setConnection($args['connection']);
        $this->_setMetadata($args['metadata']);
        $this->_setFactory($args['factory']);
        $this->_setTargetTable($args['target_table']);
    }

    /**
     * Set connection instance
     *
     * @param Varien_Db_Adapter_Interface $connection
     * @return Enterprise_Mview_Model_Action_Mview_Create
     */
    protected function _setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->_connection  = $connection;
        return $this;
    }

    /**
     * Set metadata instance
     *
     * @param Enterprise_Mview_Model_Metadata $metadata
     * @return Enterprise_Mview_Model_Action_Changelog_Subscription_Abstract
     */
    protected function _setMetadata(Enterprise_Mview_Model_Metadata $metadata)
    {
        $this->_metadata = $metadata;
        return $this;
    }

    /**
     * Set factory instance
     *
     * @param Enterprise_Mview_Model_Factory $factory
     * @return Enterprise_Mview_Model_Action_Changelog_Subscription_Abstract
     */
    protected function _setFactory(Enterprise_Mview_Model_Factory $factory)
    {
        $this->_factory = $factory;
        return $this;
    }

    /**
     * Set target table
     *
     * @param $targetTable
     * @return Enterprise_Mview_Model_Action_Changelog_Subscription_Abstract
     * @throws InvalidArgumentException
     */
    protected function _setTargetTable($targetTable)
    {
        if (empty($targetTable)) {
            throw new InvalidArgumentException('Target table is missing');
        }

        $this->_targetTable = $targetTable;
        return $this;
    }

    /**
     * Prepare and create triggers for target_table table
     */
    protected function _createTriggers()
    {
        $sqlTrigger = $this->_factory->getMagentoDbSqlTrigger();
        foreach ($sqlTrigger->getEventTypes() as $event) {
            $body = $this->_prepareBody($event);

            // Set trigger's data
            $sqlTrigger->reset();
            $sqlTrigger->setTarget($this->_targetTable);
            $sqlTrigger->setEvent($event);

            $objTrigger = $this->_factory->getMagentoDbObjectTrigger($this->_connection, $sqlTrigger->getName());

            // Drop trigger before insert with updated body
            if ($objTrigger->isExists()) {
                $objTrigger->drop();
            }

            // Create trigger only if trigger's body is not empty
            if (!empty($body)) {
                $sqlTrigger->setBody($body);
                $this->_connection->query((string)$sqlTrigger);
            }
        }
    }

    /**
     * Prepare trigger's body
     *
     * @param string $event
     * @return string
     */
    protected function _prepareBody($event)
    {
        $collection = $this->_getSubscriberCollection()
            ->addFieldToFilter('target_table', $this->_targetTable)
            ->addMetadataToCollection();

        $result = '';
        foreach ($collection as $subscriber) {
            $result .= $this->_getInsertRow($event, $subscriber);
        }
        return $result;
    }

    /**
     * Returns subscriber collection instance
     *
     * @return Enterprise_Mview_Model_Resource_Subscriber_Collection
     */
    protected function _getSubscriberCollection()
    {
        return $this->_factory->getModel('enterprise_mview/subscriber')->getCollection();
    }

    /**
     * Generate and return trigger body's row
     *
     * @param string $event
     * @param Varien_Object $subscriber
     * @return string
     */
    protected function _getInsertRow($event, Varien_Object $subscriber)
    {
        switch ($event) {
            case Magento_Db_Sql_Trigger::SQL_EVENT_INSERT:
            case Magento_Db_Sql_Trigger::SQL_EVENT_UPDATE:
                return sprintf("INSERT IGNORE INTO %s (%s) VALUES (NEW.%s);\n",
                    $this->_connection->quoteIdentifier($subscriber->getChangelogName()),
                    $this->_connection->quoteIdentifier($subscriber->getKeyColumn()),
                    $this->_connection->quoteIdentifier($subscriber->getTargetColumn()));
            case Magento_Db_Sql_Trigger::SQL_EVENT_DELETE:
                return sprintf("INSERT IGNORE INTO %s (%s) VALUES (OLD.%s);\n",
                    $this->_connection->quoteIdentifier($subscriber->getChangelogName()),
                    $this->_connection->quoteIdentifier($subscriber->getKeyColumn()),
                    $this->_connection->quoteIdentifier($subscriber->getTargetColumn()));
            default:
                return '';
        }
    }
}
