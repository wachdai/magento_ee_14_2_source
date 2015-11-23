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

/* @var $flatResource Mage_Catalog_Model_Resource_Product_Flat */
$flatResource = Mage::getResourceModel('catalog/product_flat');
/** @var $flag Mage_Catalog_Model_Product_Flat_Flag */
$flag = Mage::helper('catalog/product_flat')->getFlag();

Mage::app()->reinitStores();
foreach(Mage::app()->getStores() as $store) {
    $storeId = (int)$store->getId();
    $flag->setStoreBuilt($storeId, $flatResource->isBuilt($storeId));
}
$flag->save();

/**
 * Create table array('catalog/product', 'url_key')
 */
/**
 * @var $this Mage_Catalog_Model_Resource_Setup
 */
$productUrlKeyTableName = array('catalog/product', 'url_key');
$table = $this->getConnection()
    ->newTable($this->getTable($productUrlKeyTableName))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        'unsigned'  => true,
    ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Product Url Key')

    ->addIndex(
        $this->getIdxName(
            $productUrlKeyTableName,
            array('entity_id', 'attribute_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'attribute_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addIndex($this->getIdxName($productUrlKeyTableName, array('attribute_id')), array('attribute_id'))
    ->addIndex($this->getIdxName($productUrlKeyTableName, array('store_id')), array('store_id'))
    ->addIndex($this->getIdxName($productUrlKeyTableName, array('entity_id')), array('entity_id'))

    ->addForeignKey(
        $this->getFkName($productUrlKeyTableName, 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $this->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $this->getFkName($productUrlKeyTableName, 'entity_id', 'catalog/product', 'entity_id'),
        'entity_id', $this->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $this->getFkName($productUrlKeyTableName, 'store_id', 'core/store', 'store_id'),
        'store_id', $this->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog Product Url Key Attribute Backend Table');

$this->getConnection()->createTable($table);

/**
 * Create table array('catalog/category', 'url_key')
 */
$categoryUrlKeyTableName = array('catalog/category', 'url_key');
$table = $this->getConnection()
    ->newTable($this->getTable($categoryUrlKeyTableName))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        'unsigned'  => true,
    ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Category Url Key')

    ->addIndex(
        $this->getIdxName(
            $categoryUrlKeyTableName,
            array('entity_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($this->getIdxName($categoryUrlKeyTableName, array('attribute_id')), array('attribute_id'))
    ->addIndex($this->getIdxName($categoryUrlKeyTableName, array('store_id')), array('store_id'))
    ->addIndex($this->getIdxName($categoryUrlKeyTableName, array('entity_id')), array('entity_id'))

    ->addForeignKey(
        $this->getFkName($categoryUrlKeyTableName, 'attribute_id', 'eav/attribute', 'attribute_id'),
        'attribute_id', $this->getTable('eav/attribute'), 'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $this->getFkName($categoryUrlKeyTableName, 'entity_id', 'catalog/category', 'entity_id'),
        'entity_id', $this->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $this->getFkName($categoryUrlKeyTableName, 'store_id', 'core/store', 'store_id'),
        'store_id', $this->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog Category Url Key Attribute Backend Table');

$this->getConnection()->createTable($table);

$attributesUpdateData = array(
    Mage_Catalog_Model_Product::ENTITY => $productUrlKeyTableName,
    Mage_Catalog_Model_Category::ENTITY => $categoryUrlKeyTableName
);

foreach ($attributesUpdateData as $entityCode => $tableName) {
    $entityTypeId = $this->getEntityTypeId($entityCode);
    $attributeId = $this->getAttributeId($entityTypeId, 'url_key');
    $this->updateAttribute($entityTypeId, $attributeId, 'backend_table', $this->getTable($tableName));
}




/**
 * @var $this Mage_Catalog_Model_Resource_Setup
 */
$row = array('backend_model' => 'enterprise_catalog/product_attribute_backend_urlkey');
$this->getConnection()->update(
    $this->getTable('eav_attribute'),
    $row,
    array(
        'backend_model= ?' => 'catalog/product_attribute_backend_urlkey',
    )
);

/**
 * @var $this Mage_Catalog_Model_Resource_Setup
 */
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


/**
 * Applying data updates from Mage Catalog (they must be executed exactly here)
 */
/** @var $this Mage_Catalog_Model_Resource_Setup */

$connection = $this->getConnection();

$entityTypeId = $this->getEntityTypeId(Mage_Catalog_Model_Category::ENTITY);
$attributeId = $this->getAttributeId($entityTypeId, 'url_key');

$select = $connection->select()
    ->from(
        $this->getTable(array('catalog/category', 'varchar')),
        array('entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value')
    )
    ->where('attribute_id = ?', $attributeId);

$insertQuery = $connection->insertFromSelect(
    $select, $this->getTable($categoryUrlKeyTableName),
    array('entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value')
);

$connection->query($insertQuery);

$tableName = 'catalog/product';
$entityCode = str_replace('/', '_', $tableName);
$tmpProductUrlKeyTableName = 'tmp_' . $entityCode . '_url_key';

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


$tmpSortedProductUrlKeyVarchar = 'tmp_sorted_product_url_key_varchar';

$tmpSortedUrlKeyTable = $this->getConnection()
    ->newTable($tmpSortedProductUrlKeyVarchar)
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Value ID');


$this->getConnection()->createTable($tmpSortedUrlKeyTable);

$oldUrlKeysSelect = $this->getConnection()->select()
    ->from(array('vc' => $this->getTable(array('catalog/product', 'varchar'))), array('value_id'))
    ->where('attribute_id = ?', $attributeId)
    ->order(new Zend_Db_Expr('length(value) ASC'))
    ->order('value')
    ->order('entity_id')
    ->order('store_id');

$insert = $this->getConnection()->insertFromSelect($oldUrlKeysSelect, $tmpSortedProductUrlKeyVarchar);

$this->getConnection()->query($insert);


$caseSql = $this->getConnection()->getCaseSql('',
    array('uk.inc IS NULL OR '
    . $this->getConnection()->quoteIdentifier('m.value') . ' = 1' => new Zend_Db_Expr("''")),
    $this->getConnection()->getConcatSql(array("'-'", 'uk.inc'))
);
$urlKeySql = $this->getConnection()->getConcatSql(array('vc.value', $caseSql));
$moveSelect = $this->getConnection()->select()
    ->from(array('vc' => $this->getTable(array('catalog/product', 'varchar'))),
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
        ->from(array('vc' => $tmpSortedProductUrlKeyVarchar), array('value_id'))
        ->limit($batchSize, $offset);
    $urlKeys = $this->getConnection()->query($oldUrlKeysSelect)->fetchAll();
    $offset += $batchSize;

    foreach ($urlKeys as $key) {
        $this->getConnection()->query($insert, array('value_id' => $key['value_id']));
    }
} while (count($urlKeys));

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

/**
 * Create enterprise_catalog_redirect_category table
 */
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_catalog/category'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Relation Id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Category Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Store Id')
    ->addColumn('url_rewrite_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Rewrite Id')
    ->addIndex(
        $this->getIdxName(
            'enterprise_catalog/category', array('category_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('category_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $this->getFkName(
            'enterprise_catalog/category', 'url_rewrite_id',
            'enterprise_urlrewrite/url_rewrite', 'url_rewrite_id'
        ),
        'url_rewrite_id',
        $this->getTable('enterprise_urlrewrite/url_rewrite'),
        'url_rewrite_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION
    )
    ->setComment('Relation between rewrites and categories');

$this->getConnection()->createTable($table);

/**
 * Create enterprise_catalog_redirect_product table
 */
$table = $this->getConnection()
    ->newTable($this->getTable('enterprise_catalog/product'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Relation Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Product Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Store Id')
    ->addColumn('url_rewrite_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true,
    ), 'Rewrite Id')
    ->addIndex(
        $this->getIdxName(
            'enterprise_catalog/category', array('product_id', 'store_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('product_id', 'store_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
    $this->getFkName(
        'enterprise_catalog/product', 'url_rewrite_id',
        'enterprise_urlrewrite/url_rewrite', 'url_rewrite_id'
    ),
    'url_rewrite_id',
    $this->getTable('enterprise_urlrewrite/url_rewrite'),
    'url_rewrite_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_NO_ACTION
)
    ->setComment('Relation between rewrites and products');

$this->getConnection()->createTable($table);

$rows = $this->getConnection()->fetchAll(
    $this->getConnection()->select()
        ->from($this->getTable('core/config_data'))
        ->where('path IN (?)',
            array(
                Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX,
                Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX
            )
        )
        ->where('value <>  \'\'')
        ->where('value IS NOT NULL')
);

foreach ($rows as $row) {
    $row['value'] = ltrim($row['value'], '.'); // remove dot "." from start
    $this->getConnection()->update($this->getTable('core/config_data'), $row, 'config_id=' . $row['config_id']);
}

/** @var $metadata Enterprise_Mview_Model_Metadata */
$metadata = Mage::getModel('enterprise_mview/metadata');
$metadata->setViewName('enterprise_url_rewrite_category')
    ->setTableName($this->getTable('enterprise_url_rewrite_category'))
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

$metadata = Mage::getModel('enterprise_mview/metadata');
$metadata->setViewName('enterprise_url_rewrite_product')
    ->setTableName($this->getTable('enterprise_url_rewrite_product'))
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

/* @var  $client Enterprise_Mview_Model_Client */
$client = Mage::getModel('enterprise_mview/client');
$client->init('enterprise_url_rewrite_product');
$client->getMetadata()
    ->setKeyColumn('entity_id')
    ->setGroupCode('catalog_url_product')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

$client->execute('enterprise_urlrewrite/index_action_url_rewrite_changelog_create', array(
    'table_name' => $this->getTable(array('catalog/product', 'url_key')),
));

$client->execute('enterprise_mview/action_changelog_subscription_create', array(
    'target_table'  => $this->getTable(array('catalog/product', 'url_key')),
    'target_column' => 'entity_id'
));


/* @var $client Enterprise_Mview_Model_Client */
$client = Mage::getModel('enterprise_mview/client');
$client->init('enterprise_url_rewrite_category');
$client->getMetadata()
    ->setKeyColumn('entity_id')
    ->setGroupCode('catalog_url_category')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

$client->execute('enterprise_urlrewrite/index_action_url_rewrite_changelog_create', array(
    'table_name' => $this->getTable(array('catalog/category', 'url_key'))
));

$client->execute('enterprise_mview/action_changelog_subscription_create', array(
    'target_table'  => $this->getTable(array('catalog/category', 'url_key')),
    'target_column' => 'entity_id'
));

/** @var $client Enterprise_Mview_Model_Client */
$client = Mage::getModel('enterprise_mview/client');
$client->init('catalog_category_product_index');
$client->getMetadata()
    ->setKeyColumn('product_id')
    ->setViewName('catalog_category_product_view')
    ->setGroupCode('catalog_category_product')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

$client->execute('enterprise_catalog/index_action_catalog_category_product_changelog_create', array(
    'table_name' => $this->getTable('catalog/category_product_index'),
));

$productSubscriptions = array(
    $this->getTable('catalog/category_product')      => 'product_id',
    $this->getTable(array('catalog/product', 'int')) => 'entity_id',
);

foreach($productSubscriptions as $targetTable => $targetColumn) {
    $arguments = array(
        'target_table'  => $targetTable,
        'target_column' => $targetColumn,
    );
    $client->execute('enterprise_mview/action_changelog_subscription_create', $arguments);
}

$client->init('catalog_category_product_cat');
$client->getMetadata()
    ->setKeyColumn('category_id')
    ->setViewName('catalog_category_product_cat_view')
    ->setGroupCode('catalog_category_product')
    ->setStatus(Enterprise_Mview_Model_Metadata::STATUS_INVALID)
    ->save();

$client->execute('enterprise_catalog/index_action_catalog_category_product_changelog_create', array(
    'table_name' => $this->getTable('catalog/category_product_index'),
));

$categorySubscriptions = array(
    $this->getTable('catalog/category')               => 'entity_id',
    $this->getTable(array('catalog/category', 'int')) => 'entity_id',
);
foreach($categorySubscriptions as $targetTable => $targetColumn) {
    $arguments = array(
        'target_table'  => $targetTable,
        'target_column' => $targetColumn,
    );
    $client->execute('enterprise_mview/action_changelog_subscription_create', $arguments);
}

$events = array();
/** @var $eventModel  Enterprise_Mview_Model_Event */
$eventCollection = Mage::getModel('enterprise_mview/event')->getCollection()->load();
foreach ($eventCollection as $event) {
    /** @var $event Enterprise_Mview_Model_Event */
    $events[$event->getName()] = $event->getMviewEventId();
}

$eventsMetadataMapping = array(
    'catalog_category_product_index' => array(
        'add_store',
        'delete_store',
        'delete_store_group',
        'delete_website',
    ),
);
/** @var $metadataModel Enterprise_Mview_Model_Metadata */
$metadataModel = Mage::getModel('enterprise_mview/metadata');
foreach ($eventsMetadataMapping as $indexTable => $mappedEvents) {
    $metadataModel->load($this->getTable($indexTable), 'table_name');
    foreach ($mappedEvents as $eventName) {
        $data = array(
            'mview_event_id' => $events[$eventName],
            'metadata_id'    => $metadataModel->getId(),
        );
        $this->getConnection()->insert($this->getTable('enterprise_mview/metadata_event'), $data);
    }
}
