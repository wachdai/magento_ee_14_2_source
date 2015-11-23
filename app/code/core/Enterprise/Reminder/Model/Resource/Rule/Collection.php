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
 * @package     Enterprise_Reminder
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reminder rules resource collection model
 *
 * @category    Enterprise
 * @package     Enterprise_Reminder
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reminder_Model_Resource_Rule_Collection extends Mage_Rule_Model_Resource_Rule_Collection_Abstract
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = array(
        'website' => array(
            'associations_table' => 'enterprise_reminder/website',
            'rule_id_field'      => 'rule_id',
            'entity_id_field'    => 'website_id'
        )
    );

    /**
     * Set resource model
     */
    protected function _construct()
    {
        $this->_init('enterprise_reminder/rule');
        $this->addFilterToMap('rule_id', 'main_table.rule_id');
    }

    /**
     * Limit rules collection by date columns
     *
     * @param string $date
     *
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    public function addDateFilter($date)
    {
        $this->getSelect()
            ->where('from_date IS NULL OR from_date <= ?', $date)
            ->where('to_date IS NULL OR to_date >= ?', $date);

        return $this;
    }

    /**
     * Limit rules collection by separate rule
     *
     * @param int $value
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    public function addRuleFilter($value)
    {
        $this->getSelect()->where('main_table.rule_id = ?', $value);
        return $this;
    }
}
