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
 * @package     Enterprise_WebsiteRestriction
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Cleanup blocks HTML cache
 *
 * @category    Enterprise
 * @package     Enterprise_WebsiteRestriction
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_WebsiteRestriction_Model_System_Config_Backend_Active extends Mage_Core_Model_Config_Data
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'enterprise_websiterestriction_config_active';

    /**
     * Cleanup blocks HTML cache if value has been changed
     *
     * @return Enterprise_WebsiteRestriction_Model_System_Config_Backend_Active
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            Mage::dispatchEvent('clean_cache_by_tags', array('tags' => array(
                Mage_Core_Model_Store::CACHE_TAG,
                Mage_Cms_Model_Block::CACHE_TAG
            )));
        }
        return parent::_afterSave();
    }
}
