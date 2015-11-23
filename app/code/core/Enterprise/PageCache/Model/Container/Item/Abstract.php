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

/**
 * Placeholder container for catalog product lists
 */
abstract class Enterprise_PageCache_Model_Container_Item_Abstract
    extends Enterprise_PageCache_Model_Container_Advanced_Abstract
{
    /**
     * Parent Block
     *
     * @var Enterprise_TargetRule_Block_Catalog_Product_List_Abstract
     */
    private $_parentBlock;

    /**
     * Info cache Id
     *
     * @var string
     */
    private $_infoCacheId;

    /**
     * @var Varien_Object
     */
    protected $_transport;

    /**
     * Get parent (container) block
     *
     * @return false|Enterprise_TargetRule_Block_Catalog_Product_List_Abstract
     */
    protected function _getParentBlock()
    {
        if (is_null($this->_parentBlock)) {
            $blockType = $this->_getListBlockType();
            $this->_parentBlock = $blockType ? Mage::app()->getLayout()->createBlock($blockType) : false;
        }

        return $this->_parentBlock;
    }

    /**
     * Get parent block type
     *
     * @return string
     */
    abstract protected function _getListBlockType();

    /**
     * Get name in layout
     *
     * @return string
     */
    abstract protected function _getBlockNameInLayout();

    /**
     * Render element that was not cached
     *
     * @return false|string
     */
    protected function _renderBlock()
    {
        $product = Mage::getModel('catalog/product')->load($this->_getProductId());
        if (!Mage::registry('product') && $product) {
            Mage::register('product', $product);
        }

        $itemId = $this->_placeholder->getAttribute('item_id');
        $item = $this->getItemById($itemId);

        $layout = $this->_getLayout('catalog_product_view');
        $block = $layout->getBlock($this->_getBlockNameInLayout());
        $block->setSkipRenderTag(true);
        $block->setItem($item);
        $this->setPlaceholderBlock($block);

        Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));
        $html = $block->toHtml();

        $transport = $this->_getTransport();
        $transport->setHtml($html);
        Mage::dispatchEvent('fpc_linked_block_render_after',
            array('block' => $block, 'placeholder' => $this->_placeholder, 'transport' => $transport)
        );
        $html = $transport->getHtml();

        return $html;
    }

    /**
     * Get transport object
     *
     * @return Varien_Object
     */
    protected function _getTransport()
    {
        if (is_null($this->_transport)) {
            $this->_transport = new Varien_Object;
        }
        return $this->_transport;
    }

    /**
     * Get Item by Id from collection
     *
     * @param $itemId
     * @return null | Mage_Catalog_Model_Product
     */
    public function getItemById($itemId)
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addIdFilter($itemId)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addUrlRewrite()
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        return $collection->getFirstItem();
    }

    /**
     * Get ItemId from List
     *
     * @return string
     */
    protected function _getItemId()
    {
        $itemId = $this->_placeholder->getAttribute('item_id');
        if (!$itemId && $blockItem = $this->_getPlaceHolderBlock()->getItem()) {
            $itemId = $blockItem->getId();
        }
        return $itemId;
    }

    /**
     * Retrieve cache id
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return md5(
            $this->_placeholder->getName()
                . '_' . $this->_getProductId()
        );
    }

    /**
     * Get container individual additional cache id
     *
     * @return false|string
     */
    protected function _getAdditionalCacheId()
    {
        return md5('PRODUCT_ITEM_' . $this->_getItemId());
    }

    /**
     * Randomize cached items
     *
     * @return bool
     */
    protected function _randomizeItem()
    {
        $cachedInfo = $this->_loadInfoCache();

        if (!$cachedInfo || !is_array($cachedInfo)) {
            return false;
        }
        if (!array_key_exists('ids', $cachedInfo) || !array_key_exists('shuffle', $cachedInfo)) {
            return false;
        }
        if (!$cachedInfo['shuffle']) {
            return false;
        }

        $ids = $cachedInfo['ids'];
        $usedIdsKey = $this->_placeholder->getName() . 'used_ids';
        $usedIds = Mage::registry($usedIdsKey) ? Mage::registry($usedIdsKey) : array();
        Mage::unregister($usedIdsKey);
        if (count($ids) < 2) {
            return false;
        }

        $diff = array_values(array_diff($ids, $usedIds));
        if (count($diff) > 1) {
            shuffle($diff);
        }
        if (count($diff)) {
            $usedIds[] = $diff[0];
            $this->_placeholder->setAttribute('item_id', $diff[0]);
            Mage::register($usedIdsKey, $usedIds);
        }
        return true;
    }

    /**
     * Generate placeholder content before application was initialized and apply to page content if possible
     *
     * @param string $content
     * @return bool
     */
    public function applyWithoutApp(&$content)
    {
        $this->_randomizeItem();
        return parent::applyWithoutApp($content);
    }

    /**
     * Save cache info for items list, for randomizing
     *
     * @return Enterprise_PageCache_Model_Container_CatalogProductItem
     */
    protected function _prepareListItems()
    {
        $data = array();
        $cacheRecord = Enterprise_PageCache_Model_Container_Abstract::_loadCache($this->_getCacheId());
        if ($cacheRecord) {
            $cacheRecord = json_decode($cacheRecord, true);
            if ($cacheRecord) {
                $data = $cacheRecord;
            }
        }
        $data[$this->_getInfoCacheId()]['ids'] = $this->_getParentBlock()->getAllIds();
        $data[$this->_getInfoCacheId()]['shuffle'] = $this->_getParentBlock()->isShuffled();
        $data = json_encode($data);
        $tags = array_merge(
            $this->_getParentBlock()->getCacheTags(),
            array(Enterprise_PageCache_Model_Processor::CACHE_TAG)
        );
        $lifetime = $this->_placeholder->getAttribute('cache_lifetime');
        if (!$lifetime) {
            $lifetime = false;
        }
        Enterprise_PageCache_Model_Cache::getCacheInstance()->save($data, $this->_getCacheId(), $tags, $lifetime);
        return $this;
    }

    /**
     * Generate and apply container content in controller after application is initialized
     *
     * @param string $content
     * @return bool
     */
    public function applyInApp(&$content)
    {
        if (parent::applyInApp($content)) {
            $this->_prepareListItems();
            return true;
        }
        return false;
    }

    /**
     * Returns cache identifier for informational data about product lists
     *
     * @return string
     */
    protected function _getInfoCacheId()
    {
        if (is_null($this->_infoCacheId)) {
            $this->_infoCacheId = 'CATALOG_PRODUCT_LIST_SHARED_'
                . md5($this->_placeholder->getName()
                    . $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CART, '')
                    . $this->_getProductId());
        }
        return $this->_infoCacheId;
    }

    /**
     * Load informational cache
     *
     * @return false|array
     */
    protected function _loadInfoCache()
    {
        $result = false;
        $data = array();
        $cacheRecord = Enterprise_PageCache_Model_Container_Abstract::_loadCache($this->_getCacheId());
        if ($cacheRecord) {
            $cacheRecord = json_decode($cacheRecord, true);
            if ($cacheRecord) {
                $data = $cacheRecord;
            }
        }
        if (array_key_exists($this->_getInfoCacheId(), $data)) {
            $result = $data[$this->_getInfoCacheId()];
        }
        return $result;
    }
}

