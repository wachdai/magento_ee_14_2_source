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
 * This tool creates URL redirects(301) for URLs that have been changed during upgrade.
 * Supplementary utility.
 */

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Migration.php';

if (count($argv) != 3 || !is_numeric($argv[1])|| !is_numeric($argv[2])) {
    echo "Wrong parameters passed.";
    exit(100);
}
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('memory_limit', -1);

$page = $argv[1];
$batchSize = $argv[2];

$response = new Mage_Core_Controller_Response_Http;
Mage::app()->setResponse($response);

$migration = new Mage_Migration();

$rewritesSelect = $migration->getConnection()->select()
    ->from($migration->getResource()->getTableName('core_url_rewrite'), array(
            'product_id',
            'category_id',
            'store_id',
            'request_path'
        )
    )
    ->order('url_rewrite_id')
    ->limit($batchSize, $page * $batchSize);

foreach ($migration->getConnection()->fetchAll($rewritesSelect) as $rewriteInfo) {
    $migration->createRedirectForOriginal($rewriteInfo);
}

exit(0);
