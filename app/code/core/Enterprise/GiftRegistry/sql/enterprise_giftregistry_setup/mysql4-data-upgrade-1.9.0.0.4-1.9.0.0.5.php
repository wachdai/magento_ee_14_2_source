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

$installer = $this;
/* @var $installer Enterprise_GiftRegistry_Model_Mysql4_Setup */

$select = Mage::getModel('enterprise_giftregistry/item')->getCollection()->getSelect();
$stmt = $this->getConnection()->query($select);
$stmt->setFetchMode(Zend_Db::FETCH_ASSOC);
$itemPrototype = Mage::getModel('enterprise_giftregistry/item');
while ($currentItemRawData = $stmt->fetch()) {
        $currentItem = clone $itemPrototype;
        $currentItem->setData($currentItemRawData);
        $request = $currentItem->getCustomOptions();
        $currentItem->setCustomOptions('');
        if ($request) {
            $itemProduct = Mage::getModel('catalog/product')->load($currentItem->getProductId());
            $buyRequest = new Varien_Object(unserialize($request));
            // Convert buyRequest for each gift registry item into a list of options
            $candidate = $itemProduct->getTypeInstance(true)->prepareForCart($buyRequest, $itemProduct);
            if ($candidate && is_array($candidate)) {
                $candidate = array_shift($candidate);
                $currentItem->setOptions($candidate->getCustomOptions());
                $currentItem->save();
            }
        }
        unset($currentItem);
}
