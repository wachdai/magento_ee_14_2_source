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
 * @package     Enterprise_PageCache
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_PageCache_Model_Validator
{
    /**#@+
     * XML paths for lists of change nad delete dependencies
     */
    const XML_PATH_DEPENDENCIES_CHANGE = 'adminhtml/cache/dependency/change';
    const XML_PATH_DEPENDENCIES_DELETE = 'adminhtml/cache/dependency/delete';
    /**#@-*/

    /**
     * Data change dependency
     *
     * @deprecated after 1.12.0.2
     * @var array
     */
    protected $_dataChangeDependency = array(
        'Mage_Catalog_Model_Product',
        'Mage_Catalog_Model_Category',
        'Mage_Catalog_Model_Resource_Eav_Attribute',
        'Mage_Tag_Model_Tag',
        'Mage_Review_Model_Review',
        'Enterprise_Cms_Model_Hierarchy_Node',
        'Enterprise_Banner_Model_Banner',
        'Mage_Core_Model_Store_Group',
        'Mage_Poll_Model_Poll',
    );

    /**
     * Data delete dependency
     *
     * @deprecated after 1.12.0.2
     * @var array
     */
    protected $_dataDeleteDependency = array(
        'Mage_Catalog_Model_Category',
        'Mage_Catalog_Model_Resource_Eav_Attribute',
        'Mage_Tag_Model_Tag',
        'Mage_Review_Model_Review',
        'Enterprise_Cms_Model_Hierarchy_Node',
        'Enterprise_Banner_Model_Banner',
        'Mage_Core_Model_Store_Group',
        'Mage_Poll_Model_Poll',
    );

    protected $_skipCleanCache = array(
        'Mage_Catalog_Model_Category',
        'Mage_Catalog_Model_Product',
    );

    /**
     * Mark full page cache as invalidated
     *
     * @deprecated after 1.12.0.2
     */
    protected function _invelidateCache()
    {
        $this->_invalidateCache();
    }

    /**
     * Mark full page cache as invalidated
     *
     */
    protected function _invalidateCache()
    {
        Mage::app()->getCacheInstance()->invalidateType('full_page');
    }

    /**
     * Get list of all classes related with object instance
     *
     * @param $object
     * @return array
     */
    protected function _getObjectClasses($object)
    {
        $classes = array();
        if (is_object($object)) {
            $classes[] = get_class($object);
            $parent = $object;
            while ($parentClass = get_parent_class($parent)) {
                $classes[] = $parentClass;
                $parent = $parentClass;
            }
        }
        return $classes;
    }

    /**
     * Check if during data change was used some model related with page cache and invalidate cache
     *
     * @param mixed $object
     * @return Enterprise_PageCache_Model_Validator
     */
    public function checkDataChange($object)
    {
        $classes = $this->_getObjectClasses($object);
        $intersect = array_intersect($this->_getDataChangeDependencies(), $classes);
        if (!empty($intersect)) {
            $this->_invalidateCache();
        }

        return $this;
    }

    /**
     * Check if during data delete was used some model related with page cache and invalidate cache
     *
     * @param mixed $object
     * @return Enterprise_PageCache_Model_Validator
     */
    public function checkDataDelete($object)
    {
        $classes = $this->_getObjectClasses($object);
        $intersect = array_intersect($this->_getDataDeleteDependencies(), $classes);
        if (!empty($intersect)) {
            $this->_invalidateCache();
        }
        return $this;
    }

    /**
     * Clean cache by entity tags
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Enterprise_PageCache_Model_Validator
     */
    public function cleanEntityCache(Mage_Core_Model_Abstract $object)
    {
        $classes = $this->_getObjectClasses($object);
        $intersect = array_intersect($this->_skipCleanCache, $classes);
        if (empty($intersect)) {
            $tags = $object->getCacheIdTags();
            if (!empty($tags)) {
                $this->_getCacheInstance()->clean($tags);
            }
        }
        return $this;
    }

    /**
     * Retrieves cache instance
     *
     * @return Mage_Core_Model_Cache
     */
    protected function _getCacheInstance()
    {
        return Enterprise_PageCache_Model_Cache::getCacheInstance();
    }

    /**
     * Returns array of data change dependencies from config
     *
     * @return array
     */
    protected function _getDataChangeDependencies()
    {
        return $this->_getDataDependencies(self::XML_PATH_DEPENDENCIES_CHANGE);
    }

    /**
     * Returns array of data delete dependencies from config
     *
     * @return array
     */
    protected function _getDataDeleteDependencies()
    {
        return $this->_getDataDependencies(self::XML_PATH_DEPENDENCIES_DELETE);
    }

    /**
     * Get data dependencies by xpath
     *
     * @param string $xpath
     * @return array
     */
    protected function _getDataDependencies($xpath)
    {
        $node = Mage::getConfig()->getNode($xpath);
        return (!$node)? array() : array_values($node->asArray());
    }
}
