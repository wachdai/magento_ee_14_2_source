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
 * Since 1.13 new urls processing behaviour was introduced.
 * This tool creates URL redirects(301) for URLs that has been changed during upgrade.
 */

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Migration.php';

$attributeProduct = Mage::getModel('catalog/product')->getResource()->getAttribute('url_key');
$attributeCategory = Mage::getModel('catalog/category')->getResource()->getAttribute('url_key');
if ($attributeProduct->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
    || $attributeCategory->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL) {
    echo 'ERROR: Scope of attribute "url_key" is set to Global. This may cause DB inconsistency. Aborting.' . PHP_EOL;
    exit(255);
}
exec('php --version', $output, $status);
if ($status !== 0) {
    echo "ERROR: PHP interpreter isn't in your include path. Add it there before running the tool." . PHP_EOL;
    exit(255);
}


if (isset($argv[1]) && intval($argv[1]) > 0) {
    $threadCount = intval($argv[1]);
} else {
    $threadCount = 1;
}


$writer = new Zend_Log_Writer_Stream('php://output');
$writer->setFormatter(new Zend_Log_Formatter_Simple('[%priorityName%]: %message%' . PHP_EOL));
$logger = new Zend_Log($writer);

$logger->info('Initialization...');

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('memory_limit', -1);

$response = new Mage_Core_Controller_Response_Http;
Mage::app()->setResponse($response);

$migration = new Mage_Migration();

$processedCategories = array();
$processedProducts = array();

Mage::getModel('enterprise_mview/client')
    ->init('enterprise_url_rewrite_category')
    ->execute('enterprise_catalog/index_action_url_rewrite_category_refresh');
Mage::getModel('enterprise_mview/client')
    ->init('enterprise_url_rewrite_product')
    ->execute('enterprise_catalog/index_action_url_rewrite_product_refresh');
Mage::getModel('enterprise_mview/client')
    ->init('enterprise_url_rewrite_redirect')
    ->execute('enterprise_urlrewrite/index_action_url_rewrite_redirect_refresh');

$tableProducts = $migration->getConnection()
    ->newTable($migration->getEntityMigrationTable(Mage_Migration::ENTITY_TYPE_PRODUCT))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_CHAR, 512);
$tableCategories = $migration->getConnection()
    ->newTable($migration->getEntityMigrationTable(Mage_Migration::ENTITY_TYPE_CATEGORY))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_CHAR, 512);
if ($migration->getConnection()->isTableExists($tableProducts->getName())) {
    $migration->getConnection()->dropTable($tableProducts->getName());
}
if ($migration->getConnection()->isTableExists($tableCategories->getName())) {
    $migration->getConnection()->dropTable($tableCategories->getName());
}
$migration->getConnection()->createTable($tableProducts);
$migration->getConnection()->createTable($tableCategories);


$rewritesSelect = $migration->getConnection()->select()
    ->from($migration->getResource()->getTableName('core_url_rewrite'), array(
            'product_id',
            'category_id',
            'store_id',
            'request_path'
        )
    )
    ->order('url_rewrite_id');

$cpbAdapter = new Zend_ProgressBar_Adapter_Console(
    array(
        'elements'=> array(
            Zend_ProgressBar_Adapter_Console::ELEMENT_PERCENT,
            Zend_ProgressBar_Adapter_Console::ELEMENT_BAR,
            Zend_ProgressBar_Adapter_Console::ELEMENT_ETA,
            Zend_ProgressBar_Adapter_Console::ELEMENT_TEXT
        )
    )
);
$countSelect = clone $rewritesSelect;
$countSelect->reset(Zend_Db_Select::COLUMNS);
$countSelect->columns('COUNT(*)');

$row = $migration->getConnection()->fetchRow($countSelect);
$totalRows = array_shift($row);
unset($countSelect);


$logger->info('Renaming conflicting entities...');
$batchSize = 2000;

$batches = ceil($totalRows / $batchSize);
$progressBar = new Zend_ProgressBar($cpbAdapter, 0, $batches);
$i = 0;

$child = 'php -f ' . dirname(__FILE__) . '/umt113_conflict.php --';
$processesCheck = "ps x | grep \"$child\" | grep -v grep";
for(; $batches; $batches--) {
    $output = '';
    if ($threadCount == 1) {
        exec($child . " $i $batchSize >> " . dirname(__FILE__) . "/url_migration.log 2>&1", $output, $status);
    } else {
        waitForChildren($processesCheck, $threadCount);
        exec($child . " $i $batchSize >> " . dirname(__FILE__) . "/url_migration.log 2>&1 &", $output, $status);
    }
    $progressBar->update(++$i, '');
}

if ($threadCount != 1) {
    waitForChildren($processesCheck, 1);
}
$progressBar->finish();

$cpbAdapter = new Zend_ProgressBar_Adapter_Console(
    array(
        'elements'=> array(
            Zend_ProgressBar_Adapter_Console::ELEMENT_PERCENT,
            Zend_ProgressBar_Adapter_Console::ELEMENT_BAR,
            Zend_ProgressBar_Adapter_Console::ELEMENT_ETA,
            Zend_ProgressBar_Adapter_Console::ELEMENT_TEXT
        )
    )
);

$logger->info('Creating redirects from previous version...');
$batches = ceil($totalRows / $batchSize);
$categoriesProgressBar = new Zend_ProgressBar($cpbAdapter, 0, $batches);
$child = 'php -f ' . dirname(__FILE__) . '/umt113_redirect.php --';
$processesCheck = "ps x | grep \"$child\" | grep -v grep";
$i = 0;
for(; $batches; $batches--) {
    $output = '';
    if ($threadCount == 1) {
        exec($child . " $i $batchSize >> " . dirname(__FILE__) . "/url_migration.log 2>&1", $output, $status);
    } else {
        waitForChildren($processesCheck, $threadCount);
        exec($child . " $i $batchSize >> " . dirname(__FILE__) . "/url_migration.log 2>&1 &", $output, $status);
    }
    $categoriesProgressBar->update(++$i, '');
}

if ($threadCount != 1) {
    waitForChildren($processesCheck, 1);
}
$categoriesProgressBar->finish();

$cnxConfig = Mage::getConfig()->getResourceConnectionConfig(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
$connection = $migration->getResource()->createConnection('fresh_connection', (string)$cnxConfig->type, $cnxConfig);
$connection->dropTable($tableProducts->getName());
$connection->dropTable($tableCategories->getName());

exit(0);


function waitForChildren($command, $count)
{
    $out = array();
    exec($command, $out);
    $processCount = count($out);
    while ($processCount >= $count) {
        sleep(4);
        $out = array();
        exec($command, $out);
        $processCount = count($out);
    }
}

