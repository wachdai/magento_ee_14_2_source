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

/** @var $installer Enterprise_GiftRegistry_Model_Resource_Setup */
$installer = $this;

$defaultTypes = array(
    '1' => 'Birthday',
    '2' => 'Baby Registry',
    '3' => 'Wedding'
);
foreach ($defaultTypes as $typeId => $label) {
    $installer->getConnection()->update(
        $this->getTable('enterprise_giftregistry/info'),
        array('store_id' => Mage_Core_Model_App::ADMIN_STORE_ID),
        array(
            'type_id = ?' => $typeId,
            'store_id = ?' => Mage_Core_Model_App::DISTRO_STORE_ID,
            'label = ?' => $label
        )
    );
}
