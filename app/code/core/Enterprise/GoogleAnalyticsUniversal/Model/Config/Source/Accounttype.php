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
 * @package     Enterprise_GoogleAnalyticsUniversal
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GoogleAnalyticsUniversal_Model_Config_Source_Accounttype
{
    public function toOptionArray()
    {
        return array(
            array('value' => Enterprise_GoogleAnalyticsUniversal_Helper_Data::TYPE_UNIVERSAL,
                'label' => Mage::helper('enterprise_googleanalyticsuniversal')->__('Universal Analytics')),
            array('value' => Enterprise_GoogleAnalyticsUniversal_Helper_Data::TYPE_ANALYTICS,
                'label' => Mage::helper('enterprise_googleanalyticsuniversal')->__('Google Analytics')),
            array('value' => Enterprise_GoogleAnalyticsUniversal_Helper_Data::TYPE_TAG_MANAGER,
                'label' => Mage::helper('enterprise_googleanalyticsuniversal')->__('Google Tag Manager')),
        );
    }
}
