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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer account reward history block
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Block_Customer_Reward_History extends Mage_Core_Block_Template
{
    /**
     * History records collection
     *
     * @var Enterprise_Reward_Model_Mysql4_Reward_History_Collection
     */
    protected $_collection = null;

    /**
     * Get history collection if needed
     *
     * @return Enterprise_Reward_Model_Mysql4_Reward_History_Collection|false
     */
    public function getHistory()
    {
        if (0 == $this->_getCollection()->getSize()) {
            return false;
        }
        return $this->_collection;
    }

    /**
     * History item points delta getter
     *
     * @param Enterprise_Reward_Model_Reward_History $item
     * @return string
     */
    public function getPointsDelta(Enterprise_Reward_Model_Reward_History $item)
    {
        return Mage::helper('enterprise_reward')->formatPointsDelta($item->getPointsDelta());
    }

    /**
     * History item points balance getter
     *
     * @param Enterprise_Reward_Model_Reward_History $item
     * @return string
     */
    public function getPointsBalance(Enterprise_Reward_Model_Reward_History $item)
    {
        return $item->getPointsBalance();
    }

    /**
     * History item currency balance getter
     *
     * @param Enterprise_Reward_Model_Reward_History $item
     * @return string
     */
    public function getCurrencyBalance(Enterprise_Reward_Model_Reward_History $item)
    {
        return Mage::helper('core')->currency($item->getCurrencyAmount());
    }

    /**
     * History item reference message getter
     *
     * @param Enterprise_Reward_Model_Reward_History $item
     * @return string
     */
    public function getMessage(Enterprise_Reward_Model_Reward_History $item)
    {
        return $item->getMessage();
    }

    /**
     * History item reference additional explanation getter
     *
     * @param Enterprise_Reward_Model_Reward_History $item
     * @return string
     */
    public function getExplanation(Enterprise_Reward_Model_Reward_History $item)
    {
        return ''; // TODO
    }

    /**
     * History item creation date getter
     *
     * @param Enterprise_Reward_Model_Reward_History $item
     * @return string
     */
    public function getDate(Enterprise_Reward_Model_Reward_History $item)
    {
        return Mage::helper('core')->formatDate($item->getCreatedAt(), 'short', true);
    }

    /**
     * History item expiration date getter
     *
     * @param Enterprise_Reward_Model_Reward_History $item
     * @return string
     */
    public function getExpirationDate(Enterprise_Reward_Model_Reward_History $item)
    {
        $expiresAt = $item->getExpiresAt();
        if ($expiresAt) {
            return Mage::helper('core')->formatDate($expiresAt, 'short', true);
        }
        return '';
    }

    /**
     * Return reword points update history collection by customer and website
     *
     * @return Enterprise_Reward_Model_Mysql4_Reward_History_Collection
     */
    protected function _getCollection()
    {
        if (!$this->_collection) {
            $websiteId = Mage::app()->getWebsite()->getId();
            $this->_collection = Mage::getModel('enterprise_reward/reward_history')->getCollection()
                ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
                ->addWebsiteFilter($websiteId)
                ->setExpiryConfig(Mage::helper('enterprise_reward')->getExpiryConfig())
                ->addExpirationDate($websiteId)
                ->skipExpiredDuplicates()
                ->setDefaultOrder()
            ;
        }
        return $this->_collection;
    }

    /**
     * Instantiate Pagination
     *
     * @return Enterprise_Reward_Block_Customer_Reward_History
     */
    protected function _prepareLayout()
    {
        if ($this->_isEnabled()) {
            $pager = $this->getLayout()->createBlock('page/html_pager', 'reward.history.pager')
                ->setCollection($this->_getCollection())->setIsOutputRequired(false)
            ;
            $this->setChild('pager', $pager);
        }
        return parent::_prepareLayout();
    }

    /**
     * Whether the history may show up
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_isEnabled()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Whether the history is supposed to be rendered
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return Mage::helper('enterprise_reward')->isEnabledOnFront()
            && Mage::helper('enterprise_reward')->getGeneralConfig('publish_history');
    }
}
