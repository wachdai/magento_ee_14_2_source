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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Log items collection
 *
 * @category    Enterprise
 * @package     Enterprise_Logging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Logging_Model_Resource_Event_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_logging/event');
    }

    /**
     * Minimize usual count select
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        return parent::getSelectCountSql()->resetJoinLeft();
    }

    /**
     * Add IP filter to collection
     *
     * @param string $value
     * @return Enterprise_Logging_Model_Resource_Event_Collection
     */
    public function addIpFilter($value)
    {
        if (preg_match('/^(\d+\.){3}\d+$/', $value)
            || preg_match('/^(([0-9a-f]{1,4})?(:([0-9a-f]{1,4})?){1,}:([0-9a-f]{1,4}))(%[0-9a-z]+)?$/i', $value)
        ) {
            return $this->addFieldToFilter('ip', inet_pton($value));
        }
        $condition = $this->getConnection()->prepareSqlCondition(
            Mage::getResourceHelper('enterprise_logging')->getInetNtoaExpr('ip'),
            array('like' => Mage::getResourceHelper('core')->addLikeEscape($value, array('position' => 'any')))
        );
        $this->getSelect()->where($condition);
        return $this;
    }
}
