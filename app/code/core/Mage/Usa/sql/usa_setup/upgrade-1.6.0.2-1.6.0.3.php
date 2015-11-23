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
 * @package     Mage_Usa
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/* @var $this Mage_Core_Model_Resource_Setup */

$days = Mage::app()->getLocale()->getTranslationList('days');
$days = array_keys($days['format']['wide']);
foreach ($days as $key => $value) {
    $days[$key] = ucfirst($value);
}

$select = $this->getConnection()
    ->select()
    ->from($this->getTable('core/config_data'), array('config_id', 'value'))
    ->where('path = ?', 'carriers/dhl/shipment_days')
    ->orWhere('path = ?', 'carriers/dhl/intl_shipment_days');

foreach ($this->getConnection()->fetchAll($select) as $configRow) {
    $row = array('value' => implode(',', array_intersect_key($days, array_flip(explode(',', $configRow['value'])))));
    $this->getConnection()->update(
        $this->getTable('core/config_data'),
        $row,
        array(
            'config_id = ?' => $configRow['config_id']
        )
    );
}
