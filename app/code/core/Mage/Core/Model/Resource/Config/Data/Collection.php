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
 * @category    Mage
 * @package     Mage_Core
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Config data collection
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Config_Data_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_init('core/config_data');
    }

    /**
     * Add scope filter to collection
     *
     * @param string $scope
     * @param int $scopeId
     * @param string $section
     * @return Mage_Core_Model_Resource_Config_Data_Collection
     */
    public function addScopeFilter($scope, $scopeId, $section)
    {
        $this->addFieldToFilter('scope', $scope);
        $this->addFieldToFilter('scope_id', $scopeId);
        $this->addFieldToFilter('path', array('like' => $section . '/%'));
        return $this;
    }

    /**
     *  Add path filter
     *
     * @param string $section
     * @return Mage_Core_Model_Resource_Config_Data_Collection
     */
    public function addPathFilter($section)
    {
        $this->addFieldToFilter('path', array('like' => $section . '/%'));
        return $this;
    }

    /**
     * Add value filter
     *
     * @param int|string $value
     * @return Mage_Core_Model_Resource_Config_Data_Collection
     */
    public function addValueFilter($value)
    {
        $this->addFieldToFilter('value', array('like' => $value));
        return $this;
    }
}
