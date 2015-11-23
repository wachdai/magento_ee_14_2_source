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
 * Product url key attribute backend
 *
 * @category   Enterprise
 * @package    Enterprise_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Product_Attribute_Backend_Urlkey
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Core helper used to access core methods
     *
     * @var Mage_Core_Helper_Data
     */
    protected $_coreHelper;

    /**
     * Catalog helper instance, using for translations
     *
     * @var Enterprise_Catalog_Helper_Data
     */
    protected $_catalogHelper;

    /**
     * Current database connection
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Helper for prepare valid sql
     *
     * @var Mage_Core_Model_Resource_Helper_Abstract
     */
    protected $_eavHelper;

    /**
     * Constructor
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        if (isset($parameters['coreHelper']) && ($parameters['coreHelper'] instanceof Mage_Core_Helper_Data)) {
            $this->_coreHelper = $parameters['coreHelper'];
        } else {
            $this->_coreHelper = Mage::helper('core');
        }

        if (isset($parameters['catalogHelper']) && ($parameters['catalogHelper'] instanceof Mage_Catalog_Helper_Data)) {
            $this->_catalogHelper = $parameters['catalogHelper'];
        } else {
            $this->_catalogHelper = Mage::helper('catalog');
        }

        if (isset($parameters['connection']) && ($parameters['connection'] instanceof Varien_Db_Adapter_Interface)) {
            $this->_connection = $parameters['connection'];
        } else {
            $this->_connection  = Mage::getSingleton('core/resource')->getConnection(
                Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE
            );
        }

        if ( !empty($parameters['eavHelper'])
            && $parameters['eavHelper'] instanceof Mage_Core_Model_Resource_Helper_Abstract
        ) {
            $this->_eavHelper = $parameters['eavHelper'];
        } else {
             $this->_eavHelper = Mage::getResourceHelper('eav');
        }
    }

    /**
     * Format url_key value
     *
     * @param Mage_Catalog_Model_Abstract $object
     * @return Enterprise_Catalog_Model_Product_Attribute_Backend_Urlkey
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();

        if ($object->getIsDuplicate()) {
            $object = $this->_generateNextUrlKeySuffix($object);
        }
        $urlKey = $object->getData($attributeName);

        if (empty($urlKey)) {
            $object->setData($attributeName, $object->formatUrlKey($object->getName()));
            $object = $this->_generateNextUrlKeySuffix($object);
        } elseif (!empty($urlKey) && !$object->getIsDuplicate()) {
            $object->setData($attributeName, $object->formatUrlKey($urlKey));
        }

        $this->_validateEntityUrl($object);
        return $this;
    }

    /**
     * Check unique url_key value in catalog_category_entity_url_key table.
     *
     * @param Mage_Catalog_Model_Abstract $object
     * @return Enterprise_Catalog_Model_Product_Attribute_Backend_Urlkey
     * @throws Mage_Core_Exception
     */
    protected function _validateEntityUrl($object)
    {
        if (!$this->_isAvailableUrl($object)) {
            throw new Mage_Core_Exception(
                $this->_catalogHelper->__("Product with the '%s' url_key attribute already exists.", $object->getUrlKey())
            );
        }

        return $this;
    }

    /**
     * Change object url key according to available object id
     *
     * @param Mage_Catalog_Model_Abstract $object
     * @return string
     * @deprecated since 1.13.0.2
     */
    protected function _appendObjectIdToUrlKey($object)
    {
        $urlKey = sprintf(
            '%s-%s',
            $object->getData($this->getAttribute()->getName()),
            $object->getEntityId()
        );
        $object->setData($this->getAttribute()->getName(), $object->formatUrlKey($urlKey));

        return $object;
    }

    /**
     * Check unique url_key value in catalog_category_entity_url_key table.
     *
     * @param Mage_Catalog_Model_Abstract $object
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function _isAvailableUrl($object)
    {
        $select = $this->_connection->select()
            ->from($this->getAttribute()->getBackendTable(), array('entity_id', 'store_id'))
            ->where('value = ?', $object->getUrlKey())
            ->limit(1);
        $row = $this->_connection->fetchRow($select);

        // we should allow save same url key for product in current store view
        // but not allow save existing url key in current store view from another store view
        if (empty($row)) {
            return true;
        } elseif ($object->getId() && $object->getStoreId() !== null
                  && ($row['store_id'] == $object->getStoreId() && $row['entity_id'] == $object->getId())
        ) {
            return true;
        }
        return false;
    }

    /**
     * Generate unique url key if current url key already occupied
     *
     * @param Mage_Catalog_Model_Abstract $object
     * @return Mage_Catalog_Model_Abstract
     */
    protected function _generateNextUrlKeySuffix(Mage_Catalog_Model_Abstract $object)
    {
        $prefixValue = $object->getData($this->getAttribute()->getAttributeCode());
        $requestPathField = new Zend_Db_Expr($this->_connection->quoteIdentifier('value'));
        //select increment part of request path and cast expression to integer
        $urlIncrementPartExpression = $this->_eavHelper->getCastToIntExpression(
            $this->_connection->getSubstringSql(
                $requestPathField,
                strlen($prefixValue) + 1,
                $this->_connection->getLengthSql($requestPathField) . ' - ' . strlen($prefixValue)
            )
        );

        $prefixRegexp = preg_quote($prefixValue);
        $orCondition = $this->_connection->select()
            ->orWhere(
                $this->_connection->prepareSqlCondition(
                    'value',
                    array(
                        'regexp' => '^' . $prefixRegexp . '$',
                    )
                )
            )->orWhere(
                $this->_connection->prepareSqlCondition(
                    'value',
                    array(
                        'regexp' => '^' . $prefixRegexp . '-[0-9]*$',
                    )
                )
            )->getPart(Zend_Db_Select::WHERE);
        $select = $this->_connection->select();
        $select->from(
            $this->getAttribute()->getBackendTable(),
            new Zend_Db_Expr('MAX(ABS(' . $urlIncrementPartExpression . '))')
        )
        ->where('value LIKE :url_key')
        ->where('entity_id <> :entity_id')
        ->where(implode('', $orCondition));
        $bind = array(
            'url_key' => $prefixValue . '%',
            'entity_id' => (int) $object->getId(),
        );

        $suffix = $this->_connection->fetchOne($select, $bind);
        if (!is_null($suffix)) {
            $suffix = (int) $suffix;
            $object->setData(
                $this->getAttribute()->getAttributeCode(),
                sprintf('%s-%s', $prefixValue, ++$suffix)
            );
        }

        return $object;
    }
}
