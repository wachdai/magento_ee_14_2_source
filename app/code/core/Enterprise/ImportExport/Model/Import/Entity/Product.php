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
 * @package     Enterprise_ImportExport
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
* Import entity product model
*
* @category    Enterprise
* @package     Enterprise_ImportExport
* @author      Magento Core Team <core@magentocommerce.com>
*/
class Enterprise_ImportExport_Model_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product
{
    /**
     * Duplicate url ke error code
     */
    const ERROR_DUPLICATE_URL_KEY = 'duplicateUrlKey';

    /**
     * Invalid url key error code
     */
    const ERROR_INVALID_URL_KEY   = 'invalidUrlKey';

    /**
     * Array with all url keys (db and import file)
     *
     * @var array
     */
    protected $_urlKeys = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_initUrlKeys();
        $this->_messageTemplates = array_merge(
            $this->_messageTemplates,
            array(
                self::ERROR_DUPLICATE_URL_KEY => 'Duplicate url key',
                self::ERROR_INVALID_URL_KEY => 'Invalid value in Url key column'
            )
        );
    }

    /**
     * Initialize url keys values.
     *
     * @return Enterprise_ImportExport_Model_Import_Entity_Product
     */
    protected function _initUrlKeys()
    {
        $resource = Mage::getModel('importexport/import_proxy_product_resource');
        $select = $this->_connection->select();
        $select->from(
            array('e' => $resource->getTable('catalog_product_entity')),
            array('uk.value', 'e.sku')
        )
        ->joinInner(
            array('uk' => $resource->getTable(array('catalog_product_entity', 'url_key'))),
            'uk.entity_id = e.entity_id',
            array('')
        );
        $this->_urlKeys = $this->_connection->fetchPairs($select, array());
        return $this;
    }

    /**
     * Prepare attributes data
     *
     * @param array $rowData
     * @param int $rowScope
     * @param array $attributes
     * @param string|null $rowSku
     * @param int $rowStore
     * @return array
     */
    protected function _prepareAttributes($rowData, $rowScope, $attributes, $rowSku, $rowStore)
    {
        $rowData = $this->_prepareUrlKey($rowData, $rowScope, $rowSku);
        return parent::_prepareAttributes($rowData, $rowScope, $attributes, $rowSku, $rowStore);
    }

    /**
     * Add url key (for default store)
     *
     * @param array $rowData
     * @param int $rowScope
     * @param string $sku
     * @return array
     */
    protected function _prepareUrlKey($rowData, $rowScope, $sku)
    {
        if (self::SCOPE_DEFAULT != $rowScope) {
            return $rowData;
        }
        if (!empty($rowData['name']) && empty($rowData['url_key']) && array_search($sku, $this->_urlKeys) === false) {
            $rowData['url_key'] = Mage::getModel('catalog/product')->formatUrlKey($rowData['name']);
            if (isset($this->_urlKeys[$rowData['url_key']])) {
                $rowData['url_key'] = sprintf(
                    '%s-%s',
                    $rowData['url_key'],
                    substr(Mage::helper('core')->uniqHash(), 0, 6)
                );
            }
            $this->_urlKeys[$rowData['url_key']] = $sku;
        }
        return $rowData;
    }

    /**
     * Common validation
     *
     * @param array $rowData
     * @param int $rowNum
     * @param string|false|null $sku
     */
    protected function _validate($rowData, $rowNum, $sku)
    {
        parent::_validate($rowData, $rowNum, $sku);
        $this->_isUrlKeyValid($rowData, $rowNum, $sku);
    }
    /**
     * Check url key
     *
     * @param array $rowData
     * @param int $rowNum
     * @param string|false|null $sku
     * @return bool
     */
    protected function _isUrlKeyValid($rowData, $rowNum, $sku)
    {
        if (!empty($rowData['url_key'])) {
            if (!preg_match('#^[-0-9a-z]+$#i', $rowData['url_key'])) {
                $this->addRowError(self::ERROR_INVALID_URL_KEY, $rowNum);
                return false;
            }
            // set parent sku for child
            $sku = !empty($rowData['sku']) ? $rowData['sku'] : $sku;
            if (isset($this->_urlKeys[$rowData['url_key']])
                && ($this->_urlKeys[$rowData['url_key']] === false
                    || $this->_urlKeys[$rowData['url_key']] != $sku)) {
                $this->addRowError(self::ERROR_DUPLICATE_URL_KEY, $rowNum);
                return false;
            }
            $this->_urlKeys[$rowData['url_key']] = false;
        }
        return true;
    }
}
