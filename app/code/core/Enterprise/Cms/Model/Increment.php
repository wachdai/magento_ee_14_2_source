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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Increment model
 *
 * Description:
 * For example we operate with such entities page, version and revision.
 * We store increments for version and revision in such way for
 * each page we need separate scope of version.
 * In all version we need separate scope for revisions.
 *
 * When we store counter for version it has node = page_id and level = 0
 * When we store counter for revision it has node = version_id (not increment number) and level = 1
 * In case we will add something after revision something like sub-revision
 * we will need to use node = revision_id and level = 2  (for future).
 * Type is only one value '0' at this time bc revision control used only for pages.
 *
 * @method Enterprise_Cms_Model_Resource_Increment _getResource()
 * @method Enterprise_Cms_Model_Resource_Increment getResource()
 * @method int getType()
 * @method Enterprise_Cms_Model_Increment setType(int $value)
 * @method int getNode()
 * @method Enterprise_Cms_Model_Increment setNode(int $value)
 * @method int getLevel()
 * @method Enterprise_Cms_Model_Increment setLevel(int $value)
 * @method int getLastId()
 * @method Enterprise_Cms_Model_Increment setLastId(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Cms_Model_Increment extends Mage_Core_Model_Abstract
{
    /*
     * Increment types
     */
    const TYPE_PAGE = 0;

    /*
     * Increment levels
     */
    const LEVEL_VERSION = 0;
    const LEVEL_REVISION = 1;

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('enterprise_cms/increment');
    }

    /**
     * Init mapping array of short fields to
     * its full names
     *
     * @resturn Varien_Object
     */
    protected function _initOldFieldsMap()
    {
        $this->_oldFieldsMap = array(
            'type'  => 'increment_type',
            'node'  => 'increment_node',
            'level' => 'increment_level'
        );
    }

    /**
     * Load increment counter by passed node and level
     *
     * @param int $type
     * @param int $node
     * @param int $level
     * @return Enterprise_Cms_Model_Increment
     */
    public function loadByTypeNodeLevel($type, $node, $level)
    {
        $this->getResource()->loadByTypeNodeLevel($this, $type, $node, $level);

        return $this;
    }

    /**
     * Get incremented value of counter.
     *
     * @return mixed
     */
    protected function _getNextId()
    {
        $incrementId = $this->getLastId();
        if ($incrementId) {
            $incrementId++;
        } else {
            $incrementId = 1;
        }

        return $incrementId;
    }

    /**
     * Generate new increment id for passed type, node and level.
     *
     * @param int $type
     * @param int $node
     * @param int $level
     * @return string
     */
    public function getNewIncrementId($type, $node, $level)
    {
        $this->loadByTypeNodeLevel($type, $node, $level);

        // if no counter for such combination we need to create new
        if (!$this->getId()) {
            $this->setType($type)
                ->setNode($node)
                ->setLevel($level);
        }

        $newIncrementId = $this->_getNextId();
        $this->setLastId($newIncrementId)->save();

        return $newIncrementId;
    }
}
