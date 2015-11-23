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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/* @var $installer Enterprise_Reward_Model_Mysql4_Setup */
$installer = $this;

$attributes = array(
    'reward_update_notification',
    'reward_warning_notification'
);
$entityTypeCode = 'customer';

foreach ($attributes as $attributeCode) {
    $attributeId = $installer->getAttributeId($entityTypeCode, $attributeCode);
    $installer->updateAttribute($entityTypeCode, $attributeId, 'is_user_defined', 0);
    $installer->updateAttribute($entityTypeCode, $attributeId, 'is_system', 1);
    $installer->updateAttribute($entityTypeCode, $attributeId, 'is_hidden', 1);
}
