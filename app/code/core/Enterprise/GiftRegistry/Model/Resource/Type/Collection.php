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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Gift refistry type resource collection
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftRegistry_Model_Resource_Type_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * If the table was joined flag
     *
     * @var bool
     */
    protected $_isTableJoined                       = false;

    /**
     * Collection initialization
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftregistry/type');
    }

    /**
     * Add store data to collection
     *
     * @param int $storeId
     * @return Enterprise_GiftRegistry_Model_Resource_Type_Collection
     */
    public function addStoreData($storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        $infoTable = $this->getTable('enterprise_giftregistry/info');
        $adapter   = $this->getConnection();

        $select = $adapter->select();
        $select->from(array('m' => $this->getMainTable()))
            ->joinInner(
                array('d' => $infoTable),
                $adapter->quoteInto('m.type_id = d.type_id AND d.store_id = ?', Mage_Core_Model_App::ADMIN_STORE_ID),
                array())
            ->joinLeft(
                array('s' => $infoTable),
                $adapter->quoteInto('s.type_id = m.type_id AND s.store_id = ?', (int)$storeId),
                array(
                    'label'     => $adapter->getCheckSql('s.label IS NULL', 'd.label', 's.label'),
                    'is_listed' => $adapter->getCheckSql('s.is_listed IS NULL', 'd.is_listed', 's.is_listed'),
                    'sort_order'=> $adapter->getCheckSql('s.sort_order IS NULL', 'd.sort_order', 's.sort_order')
            ));

        $this->getSelect()->reset()->from(array('main_table' => $select));

        $this->_isTableJoined = true;

        return $this;
    }

    /**
     * Filter collection by listed param
     *
     * @return Enterprise_GiftRegistry_Model_Resource_Type_Collection
     */
    public function applyListedFilter()
    {
        if ($this->_isTableJoined) {
            $this->getSelect()->where('is_listed = ?', 1);
        }
        return $this;
    }

    /**
     * Apply sorting by sort_order param
     *
     * @return Enterprise_GiftRegistry_Model_Resource_Type_Collection
     */
    public function applySortOrder()
    {
        if ($this->_isTableJoined) {
            $this->getSelect()->order('sort_order');
        }
        return $this;
    }

    /**
     * Convert collection to array for select options
     *
     * @param bool $withEmpty
     * @return array
     */
    public function toOptionArray($withEmpty = false)
    {
        $result = $this->_toOptionArray('type_id', 'label');
        if ($withEmpty) {
            $result = array_merge(array(array(
                'value' => '',
                'label' => Mage::helper('enterprise_giftregistry')->__('-- All --')
            )), $result);
        }
        return $result;
    }
}
