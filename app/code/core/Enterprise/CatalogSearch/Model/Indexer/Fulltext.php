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
 * @package     Enterprise_CatalogSearch
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Fulltext indexer switcher
 *
 * @deprecated since version 1.13.2
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CatalogSearch_Model_Indexer_Fulltext extends Mage_CatalogSearch_Model_Indexer_Fulltext
{
    /**
     * Flag whether fulltext engine is on
     *
     * @var bool|null
     */
    protected $_fulltextOn = null;

    /**
     * Return whether fulltext engine is on
     *
     * @return bool
     */
    protected function _isFulltextOn()
    {
        if (is_null($this->_fulltextOn)) {
            $this->_fulltextOn = Mage::helper('enterprise_catalogsearch')->isFulltextOn();
        }
        return $this->_fulltextOn;
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        if ($this->_isFulltextOn()) {
            $this->_matchedEntities = array();
        }
    }
}
