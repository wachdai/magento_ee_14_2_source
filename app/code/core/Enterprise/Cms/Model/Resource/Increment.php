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
 * Increment resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Model_Resource_Increment extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('enterprise_cms/increment', 'increment_id');
    }

    /**
     * Load increment counter by passed node and level
     *
     * @param Mage_Core_Model_Abstract $object
     * @param int $type
     * @param int $node
     * @param int $level
     * @return bool
     */
    public function loadByTypeNodeLevel(Mage_Core_Model_Abstract $object, $type, $node, $level)
    {
        $read = $this->_getReadAdapter();

        $select = $read->select()->from($this->getMainTable())
            ->forUpdate(true)
            ->where(implode(' AND ', array(
                'increment_type  = :increment_type',
                'increment_node  = :increment_node',
                'increment_level = :increment_level'
             )));

        $bind = array(':increment_type'  => $type,
                      ':increment_node'  => $node,
                      ':increment_level' => $level);

        $data = $read->fetchRow($select, $bind);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);

        return true;
    }

    /**
     * Remove unneeded increment record.
     *
     * @param int $type
     * @param int $node
     * @param int $level
     * @return Enterprise_Cms_Model_Resource_Increment
     */
    public function cleanIncrementRecord($type, $node, $level)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(),
            array('increment_type = ?'  => $type,
                  'increment_node = ?'  => $node,
                  'increment_level = ?' => $level));

        return $this;
    }
}
