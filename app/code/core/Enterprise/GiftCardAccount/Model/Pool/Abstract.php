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
 * @package     Enterprise_GiftCardAccount
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 *  Enterprise_GiftCardAccount_Model_Pool_Abstract  model object definition.
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCardAccount
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_GiftCardAccount_Model_Pool_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Status Constant for Gift Card Code: FREE
     *
     * @var integer
     */
    const STATUS_FREE = 0;

    /**
     * Status Constant for Gift Card Code: USED
     *
     * @var integer
     */
    const STATUS_USED = 1;

    /**
     * Percentage of the codes in the pool already used
     *
     * @var string
     */
    protected $_pool_percent_used = null;

    /**
     * Total Pool Size
     *
     * @var integer
     */
    protected $_pool_size = 0;

    /**
     * Total number of gift codes that are still free
     *
     * @var integer
     */
    protected $_pool_free_size = 0;

    /**
     * Select an unused unique gift card code from the gift card code pool
     *
     * @return string
     */
    public function shift()
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('status', self::STATUS_FREE)
            ->setPageSize(1);
        // Lock the giftcard code collection
        $collection->getSelect()->forUpdate(true);

        $notInArray = $this->getExcludedIds();
        if (is_array($notInArray) && !empty($notInArray)) {
            $collection->addFieldToFilter('code', array('nin' => $notInArray));
        }
        $items = $collection->getItems();
        if (!$items) {
            $this->_throwException($this->helper('enterprise_giftcardaccount')->__('No codes left in the pool.'));
        }
        $item = array_shift($items);
        return $item->getId();
    }

    /**
     * Throw exception with given message
     * @param string $message
     *
     * @throws Mage_Core_Exception
     */
    protected function _throwException($message)
    {
        Mage::throwException($message);
    }

    /**
     * Retrieves helper class based on its name
     *
     * @param string $name
     * @return Mage_Core_Helper_Abstract
     */
    public function helper($name)
    {
        return Mage::helper($name);
    }

    /**
     * Load code pool usage info
     *
     * @return Varien_Object
     */
    public function getPoolUsageInfo()
    {
        if (is_null($this->_pool_percent_used)) {
            $this->_pool_size = $this->getCollection()->getSize();
            $this->_pool_free_size = $this->getCollection()
                ->addFieldToFilter('status', self::STATUS_FREE)
                ->getSize();
            if (!$this->_pool_size) {
                $this->_pool_percent_used = 100;
            } else {
                $this->_pool_percent_used = 100 - round($this->_pool_free_size / ($this->_pool_size / 100), 2);
            }
        }

        $result = new Varien_Object();
        $result
            ->setTotal($this->_pool_size)
            ->setFree($this->_pool_free_size)
            ->setPercent($this->_pool_percent_used);
        return $result;
    }

    /**
     * Delete free codes from pool
     *
     * @return Enterprise_GiftCardAccount_Model_Pool_Abstract
     */
    public function cleanupFree()
    {
        $this->getResource()->cleanupByStatus(self::STATUS_FREE);
        return $this;
    }
}
