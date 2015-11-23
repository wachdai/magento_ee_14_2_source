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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * @var $this Mage_Catalog_Model_Resource_Setup
 */

//======================================================================================================
$productUrlKeyTableName = array('catalog/product', 'url_key');
$tableName = 'catalog/product';
$entityCode = str_replace('/', '_', $tableName);
$tmpProductUrlKeyTableName = 'tmp_' . $entityCode . '_url_key';

$countSelect = $this->getConnection()->select()
    ->from(
        $this->getTable($productUrlKeyTableName),
        array('cnt' => new Zend_Db_Expr('COUNT(value)'))
    )
    ->group('value')
    ->having('cnt > ?', 1)
    ->limit(1);
$countUnique = $this->getConnection()->fetchOne($countSelect);

if ($countUnique) {
    $entityTypeId = $this->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
    $attributeId = $this->getAttributeId($entityTypeId, 'url_key');

    $tmpUrlKeyTable = $this->getConnection()
        ->createTableByDdl($this->getTable($productUrlKeyTableName), $tmpProductUrlKeyTableName);
    $tmpUrlKeyTable->addColumn('inc', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => 1,
    ), 'Increment')
        ->addIndex(
            $this->getIdxName(
                $tmpProductUrlKeyTableName,
                array('value'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('value'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        );
    $this->getConnection()->createTable($tmpUrlKeyTable);


    $tmpSortedProductUrlKey = 'tmp_sorted_product_url_key';

    $tmpSortedUrlKeyTable = $this->getConnection()
        ->newTable($tmpSortedProductUrlKey)
        ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable'  => false,
            'unsigned'  => true,
        ), 'Value ID');


    $this->getConnection()->createTable($tmpSortedUrlKeyTable);

    $oldUrlKeysSelect = $this->getConnection()->select()
        ->from(array('vc' => $this->getTable($productUrlKeyTableName)), array('value_id'))
        ->where('attribute_id = ?', $attributeId)
        ->order(new Zend_Db_Expr('length(value) ASC'))
        ->order('value')
        ->order('entity_id')
        ->order('store_id');

    $insert = $this->getConnection()
        ->insertFromSelect($oldUrlKeysSelect, $tmpSortedProductUrlKey);

    $this->getConnection()->query($insert);


    $caseSql = $this->getConnection()->getCaseSql('',
        array('uk.inc IS NULL OR '
            . $this->getConnection()->quoteIdentifier('m.value') . ' = 1' => new Zend_Db_Expr("''")),
        $this->getConnection()->getConcatSql(array("'-'", 'uk.inc'))
    );
    $urlKeySql = $this->getConnection()->getConcatSql(array('vc.value', $caseSql));
    $moveSelect = $this->getConnection()->select()
        ->from(array('vc' => $this->getTable(array('catalog/product', 'url_key'))),
            array(
                'value_id'  => 'vc.value_id',
                'entity_type_id'  => 'vc.entity_type_id',
                'attribute_id'  => 'vc.attribute_id',
                'store_id'  => 'vc.store_id',
                'entity_id'  => 'vc.entity_id',
                'value'    => new Zend_Db_Expr($urlKeySql),
            ))
        ->joinLeft(array('uk' => $tmpProductUrlKeyTableName),
            'vc.value = uk.value', array())
        ->joinLeft(array('m' => $this->getTable('enterprise_index/multiplier')),
            'uk.value IS NOT NULL', array())
        ->where('vc.value_id = :value_id');

    $insert = $this->getConnection()->insertFromSelect($moveSelect, $tmpProductUrlKeyTableName,
        array('value_id', 'entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value'));
    $insert .= sprintf(' ON DUPLICATE KEY UPDATE %1$s = %1$s + 1', $tmpProductUrlKeyTableName . '.inc');

    $batchSize = 1000;
    $offset = 0;
    do {
        $oldUrlKeysSelect = $this->getConnection()->select()
            ->from(array('vc' => $tmpSortedProductUrlKey), array('value_id'))
            ->limit($batchSize, $offset);
        $urlKeys = $this->getConnection()->query($oldUrlKeysSelect)->fetchAll();
        $offset += $batchSize;

        foreach ($urlKeys as $key) {
            $this->getConnection()->query($insert, array('value_id' => $key['value_id']));
        }
    } while (count($urlKeys));

    $this->getConnection()->truncateTable($this->getTable($productUrlKeyTableName));
    $this->getConnection()->query(
        $this->getConnection()->insertFromSelect(
            $this->getConnection()->select()
                ->from(array('uk' => $tmpProductUrlKeyTableName), array(
                    'value_id',
                    'entity_type_id',
                    'attribute_id',
                    'store_id',
                    'entity_id',
                    'value',
                )),
            $this->getTable($productUrlKeyTableName)
        )
    );
    $this->getConnection()->dropTable($tmpUrlKeyTable->getName());
    $this->getConnection()->dropTable($tmpSortedUrlKeyTable->getName());
}
//========================================================================================================

$indexes = $this->getConnection()->getIndexList($this->getTable(array('catalog/product', 'url_key')));
$indexName = $this->getIdxName(
    array('catalog/product', 'url_key'),
    array('store_id', 'value'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);
if (isset($indexes[$indexName])) {
    $this->getConnection()->dropIndex($this->getTable(array('catalog/product', 'url_key')), $indexName);
    $this->getConnection()->addIndex(
        $this->getTable(array('catalog/product', 'url_key')),
        $this->getIdxName(
            array('catalog/product', 'url_key'),
            array('value'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('value'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
}

$indexes = $this->getConnection()->getIndexList($this->getTable(array('catalog/category', 'url_key')));
$indexNames = array(
    $this->getIdxName(
        array('catalog/category', 'url_key'),
        array('store_id', 'value'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    $this->getIdxName(
        array('catalog/category', 'url_key'),
        array('value'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
);
foreach ($indexNames as $indexName) {
    if (isset($indexes[$indexName])) {
        $this->getConnection()->dropIndex($this->getTable(array('catalog/category', 'url_key')), $indexName);
    }
}

$row = array('backend_model' => 'catalog/category_attribute_backend_urlkey');
$this->getConnection()->update(
    $this->getTable('eav_attribute'),
    $row,
    array(
        'backend_model= ?' => 'enterprise_catalog/category_attribute_backend_urlkey',
    )
);

$row = array('backend_model' => 'enterprise_catalog/product_attribute_backend_urlkey');
$this->getConnection()->update(
    $this->getTable('eav_attribute'),
    $row,
    array(
        'backend_model= ?' => 'catalog/product_attribute_backend_urlkey',
    )
);
