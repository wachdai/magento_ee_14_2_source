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
 * @package     Mage_Shell
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Since 1.13.0.2 new urls processing behaviour was introduced.
 * This tool should be launched on version 1.13.0.0, before you'll start upgrade to 1.13.02
 */

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Migration.php';

$writer = new Zend_Log_Writer_Stream('php://output');
$writer->setFormatter(new Zend_Log_Formatter_Simple('[%priorityName%]: %message%' . PHP_EOL));
$logger = new Zend_Log($writer);

$logger->info('Initialization...');

$migration = new Mage_Migration();
$timeStart = microtime(true);
$redirectDescription = Mage::helper('enterprise_catalog')->__('1.13.0.0-1.13.0.2 migration redirect');


########################################################################################################################
## Categories processing
########################################################################################################################

$logger->info('Start url rewrites processing from 1.13.0.0 to 1.13.0.2 ...');

/** @var Mage_Migration_Category_Redirect $redirectProcessor */
$redirectProcessor = new Mage_Migration_Category_Redirect();

$rootCategories = array();
/** @var Mage_Core_Model_Store $store */
foreach (Mage::app()->getStores() as $store) {
    $rootCategoryId = $store->getGroup()->getRootCategoryId();
    $rootCategories[$rootCategoryId] = $rootCategoryId;
}

foreach ($rootCategories as $rootCategoryId) {
    /** @var Mage_Catalog_Model_Category $category */
    $rootCategory = Mage::getModel('catalog/category');
    $rootCategory->load($rootCategoryId);
    $categories = $rootCategory->getChildrenCategories();
    $logger->info(sprintf('Start root category "%s" processing ...', $rootCategory->getName()));
    foreach ($categories as $subCategory) {
        $redirectProcessor->saveCustomRedirects($subCategory, 0);
    }
}

########################################################################################################################

$timeEnd = microtime(true);
$total = $timeEnd - $timeStart;

$logger->info('Executed in ' . sprintf("%01.2f", $total) . ' sec');

exit(0);
