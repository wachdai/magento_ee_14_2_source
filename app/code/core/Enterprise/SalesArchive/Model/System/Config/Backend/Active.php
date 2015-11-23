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
 * @package     Enterprise_SalesArchive
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class Enterprise_SalesArchive_Model_System_Config_Backend_Active
    extends Mage_Adminhtml_Model_System_Config_Backend_Cache
{
    /**
     * Cache tags to clean
     *
     * @var array
     */
    protected $_cacheTags = array(
        Mage_Adminhtml_Block_Page_Menu::CACHE_TAGS
    );

    /**
     * Clean cache, value was changed
     *
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        if ($this->isValueChanged() && !$this->getValue()) {
            Mage::getModel('enterprise_salesarchive/archive')->removeOrdersFromArchive();
        }
        return $this;
    }

    /**
     * Get field comment
     *
     * @param string $currentValue
     * @return string
     */
    public function getCommentText($element, $currentValue)
    {
        if ($currentValue) {
            $ordersCount = Mage::getResourceSingleton('enterprise_salesarchive/order_collection')->getSize();
            if ($ordersCount) {
                return Mage::helper('enterprise_salesarchive')->__('There are %s orders in archive. All of them will be moved to regular table after archive is disabled.', $ordersCount);
            }
        }
        return '';
    }
}
