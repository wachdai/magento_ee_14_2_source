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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Cms page revision resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Model_Resource_Page_Revision extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Name of page table from config
     *
     * @var string
     */
    protected $_pageTable;

    /**
     * Name of version table from config
     *
     * @var string
     */
    protected $_versionTable;

    /**
     * Alias of page table from config
     *
     * @var string
     */
    protected $_pageTableAlias;

    /**
     * Alias of version table from config
     *
     * @var string
     */
    protected $_versionTableAlias;

    /**
     * Resource initialization. Define the table names and its aliases.
     */
    protected function _construct()
    {
        $this->_init('enterprise_cms/page_revision', 'revision_id');

        $this->_pageTable         = $this->getTable('cms/page');
        $this->_versionTable      = $this->getTable('enterprise_cms/page_version');
        $this->_pageTableAlias    = 'page_table';
        $this->_versionTableAlias = 'version_table';
    }

    /**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Enterprise_Cms_Model_Resource_Page_Revision
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCopiedFromOriginal()) {
            /*
             * For two attributes which represent datetime data in DB
             * we should make converting such as:
             * If they are empty we need to convert them into DB
             * type NULL so in DB they will be empty and not some default value.
             */
            foreach (array('custom_theme_from', 'custom_theme_to') as $dataKey) {
                $date = $object->getData($dataKey);
                if (!$date) {
                    $object->setData($dataKey, new Zend_Db_Expr('NULL'));
                }
            }
        }
        return parent::_beforeSave($object);
    }

    /**
     * Process data after save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Enterprise_Cms_Model_Resource_Page_Revision
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->_aggregateVersionData((int)$object->getVersionId());

        return parent::_afterSave($object);
    }

    /**
     * Process data after delete
     * Validate if this revision can be removed
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Enterprise_Cms_Model_Resource_Page_Revision
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        $this->_aggregateVersionData((int)$object->getVersionId());

        return parent::_afterDelete($object);
    }

    /**
     * Checking if revision was published
     *
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function isRevisionPublished(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from($this->_pageTable, 'published_revision_id')
            ->where('page_id = ?', (int)$object->getPageId());

        $result = $this->_getReadAdapter()->fetchOne($select);

        return $result == $object->getId();
    }

    /**
     * Aggregate data for version
     *
     * @param int $versionId
     * @return Enterprise_Cms_Model_Resource_Page_Revision
     */
    protected function _aggregateVersionData($versionId)
    {
        $adapter = $this->_getReadAdapter();
        $selectCount = $adapter->select()
            ->from($this->getMainTable(), array('version_id', 'revisions_count' => 'COUNT(1)'))
            ->where('version_id = ?', (int)$versionId)
            ->group('version_id');

        $sql = new Zend_Db_Expr(sprintf('(%s)', $selectCount));
        $select = clone $selectCount;
        $select->reset()
            ->join(array('r' => $sql), 'p.version_id = r.version_id', array('revisions_count'))
            ->where('r.version_id = ?', (int)$versionId);

        $adapter = $this->_getWriteAdapter();
        $query = $adapter->updateFromSelect($select, array('p' => $this->_versionTable));
        $adapter->query($query);

        return $this;
    }

    /**
     * Publishing passed revision object to page
     *
     * @param Enterprise_Cms_Model_Page_Revision $object
     * @param int $targetId
     * @return Enterprise_Cms_Model_Resource_Page_Revision
     */
    public function publish(Enterprise_Cms_Model_Page_Revision $object, $targetId)
    {
        $data      = $this->_prepareDataForTable($object, $this->_pageTable);
        $condition = array('page_id = ?' => $targetId);
        $this->_getWriteAdapter()->update($this->_pageTable, $data, $condition);

        return $this;
    }

    /**
     * Loading revision's data with extra access level checking.
     *
     * @param Enterprise_Cms_Model_Page_Revision $object
     * @param array|string $accessLevel
     * @param int $userId
     * @param int|string $value
     * @param string|null $field
     * @return Enterprise_Cms_Model_Resource_Page_Revision
     */
    public function loadWithRestrictions($object, $accessLevel, $userId, $value, $field)
    {
        if ($field === null) {
            $field = $this->getIdFieldName();
        }

        $read = $this->_getReadAdapter();
        if ($value) {
            // getting main load select
            $select = $this->_getLoadSelect($field, $value, $object);

            // prepare join conditions for version table
            $joinConditions = array($this->_getPermissionCondition($accessLevel, $userId));
            $joinConditions[] = sprintf('%s.version_id = %s.version_id',
                $this->_versionTableAlias, $this->getMainTable());
            // joining version table
            $this->_joinVersionData($select, 'joinInner', implode(' AND ', $joinConditions));

            // prepare join conditions for page table
            $joinConditions = sprintf('%s.page_id = %s.page_id', $this->getMainTable(), $this->_pageTableAlias);
            // joining page table
            $this->_joinPageData($select, 'joinInner', $joinConditions);

            if ($field != $this->getIdFieldName()) {
                // Adding limitation and ordering bc we are
                // loading not by unique conditions so we need
                // to make sure we have latest revision and only one
                $this->_addSingleLimitation($select);
            }

            $data = $read->fetchRow($select);
            if ($data) {
                $object->setData($data);
            }
        }

        $this->_afterLoad($object);
        return $this;
    }

    /**
     * Loading revision's data using version and page's id but also counting on access restrictions.
     * Used to load clean revision without any data that is under revision control but which
     * will have all other data from version and page tables.
     *
     * @param Enterprise_Cms_Model_Page_Revision $object
     * @param int $versionId
     * @param int $pageId
     * @param array|string $accessLevel
     * @param int $userId
     * @return Enterprise_Cms_Model_Resource_Page_Revision
     */
    public function loadByVersionPageWithRestrictions($object, $versionId, $pageId, $accessLevel, $userId)
    {
        $read = $this->_getReadAdapter();
        if ($versionId && $pageId) {
            // getting main load select
            $select = $this->_getLoadSelect($this->getIdFieldName(), false, $object);
            // reseting all columns and where as we don't have need them
            $select->reset(Zend_Db_Select::COLUMNS)
                   ->reset(Zend_Db_Select::WHERE);

            // adding where conditions with restriction filter
            $whereConditions = array($this->_getPermissionCondition($accessLevel, $userId));
            $whereConditions[] = $read->quoteInto($this->_versionTableAlias . '.version_id = ?', $versionId);
            $select->where(implode(' AND ', $whereConditions));

            //joining version table
            $this->_joinVersionData($select, 'joinRight', '1 = 1');

            //joining page table
            $joinCondition = $read->quoteInto($this->_pageTableAlias . '.page_id = ?', $pageId);
            $this->_joinPageData($select, 'joinLeft', $joinCondition);
            // adding page id column which we will not have as this is clean revision
            // and this column is not specified in join
            $select->columns('page_table.page_id');

            // Adding limitation and ordering bc we are
            // loading not by unique conditions so we need
            // to make sure we have latest revision and only one
            $this->_addSingleLimitation($select);

            $data = $read->fetchRow($select);
            if ($data) {
                $object->setData($data);
            }
        }

        $this->_afterLoad($object);
        return $this;
    }

    /**
     * Preparing array of conditions based on user id and version's access level.
     *
     * @param array|string $accessLevel
     * @param int $userId
     * @return string
     */
    protected function _getPermissionCondition($accessLevel, $userId)
    {
        $read = $this->_getReadAdapter();
        $permissionCondition = array();
        $permissionCondition[] = $read->quoteInto($this->_versionTableAlias . '.user_id = ? ', $userId);

        if (is_array($accessLevel) && !empty($accessLevel)) {
            $permissionCondition[] = $read->quoteInto($this->_versionTableAlias . '.access_level IN (?)', $accessLevel);
        } elseif ($accessLevel) {
            $permissionCondition[] = $read->quoteInto($this->_versionTableAlias . '.access_level = ?', $accessLevel);
        } else {
            $permissionCondition[] = $this->_versionTableAlias . '.access_level = ""';
        }

        return sprintf('(%s)', implode(' OR ', $permissionCondition));
    }

    /**
     * Joining version table using specified conditions and join type.
     *
     * @param Zend_Db_Select $select
     * @param string $joinType
     * @param string $joinConditions
     * @return Zend_Db_Select
     */
    protected function _joinVersionData($select, $joinType, $joinConditions)
    {
        $select->$joinType(array($this->_versionTableAlias => $this->_versionTable),
            $joinConditions,
            array('version_id', 'version_number', 'label', 'access_level', 'version_user_id' => 'user_id'));

        return $select;
    }

    /**
     * Joining page table using specified conditions and join type.
     *
     * @param Zend_Db_Select $select
     * @param string $joinType can be joinInner, joinRight, joinLeft
     * @param string $joinConditions
     * @return Zend_Db_Select
     */
    protected function _joinPageData($select, $joinType, $joinConditions)
    {
        $select->$joinType(array($this->_pageTableAlias => $this->_pageTable),
            $joinConditions, array('title'));

        return $select;
    }

    /**
     * Applying order by create datetime and limitation to one record.
     *
     * @param Varien_Db_Select $select
     * @return Varien_Db_Select
     */
    protected function _addSingleLimitation($select)
    {
        $select->order($this->getMainTable() . '.created_at ' . Varien_Db_Select::SQL_DESC)->limit(1);
        return $select;
    }
}
