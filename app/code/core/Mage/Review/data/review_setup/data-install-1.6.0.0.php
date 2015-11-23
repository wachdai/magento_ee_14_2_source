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
 * @package     Mage_Review
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Review data install
 *
 * @category    Mage
 * @package     Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

//Fill table review/review_entity
$reviewEntityCodes = array(
    Mage_Review_Model_Review::ENTITY_PRODUCT_CODE,
    Mage_Review_Model_Review::ENTITY_CUSTOMER_CODE,
    Mage_Review_Model_Review::ENTITY_CATEGORY_CODE,
);
foreach ($reviewEntityCodes as $entityCode) {
    $installer->getConnection()
            ->insert($installer->getTable('review/review_entity'), array('entity_code' => $entityCode));
}

//Fill table review/review_entity
$reviewStatuses = array(
    Mage_Review_Model_Review::STATUS_APPROVED       => 'Approved',
    Mage_Review_Model_Review::STATUS_PENDING        => 'Pending',
    Mage_Review_Model_Review::STATUS_NOT_APPROVED   => 'Not Approved'
);
foreach ($reviewStatuses as $k => $v) {
    $bind = array(
        'status_id'     => $k,
        'status_code'   => $v
    );
    $installer->getConnection()->insertForce($installer->getTable('review/review_status'), $bind);
}
