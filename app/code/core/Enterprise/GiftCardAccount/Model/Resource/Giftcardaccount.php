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
 * @package     Enterprise_GiftCardAccount
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * GiftCard account resource model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCardAccount
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftCardAccount_Model_Resource_Giftcardaccount extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table  and primary key field
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftcardaccount/giftcardaccount', 'giftcardaccount_id');
    }

    /**
     * Get gift card account ID by specified code
     *
     * @param string $code
     * @return mixed
     */
    public function getIdByCode($code)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select();
        $select->from($this->getMainTable(), $this->getIdFieldName());
        $select->where('code = :code');

        if ($id = $read->fetchOne($select, array('code' => $code))) {
            return $id;
        }

        return false;
    }

    /**
     * Update gift card accounts state
     *
     * @param array $ids
     * @param int $state
     * @return Enterprise_GiftCardAccount_Model_Resource_Giftcardaccount
     */
    public function updateState($ids, $state)
    {
        if (empty($ids)) {
            return $this;
        }
        $bind = array('state' => $state);
        $where[$this->getIdFieldName() . ' IN (?)'] = $ids;

        $this->_getWriteAdapter()->update($this->getMainTable(), $bind, $where);
        return $this;
    }
}
