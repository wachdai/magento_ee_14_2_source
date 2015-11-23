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
 * @package     Enterprise_Persistent
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Persistent_Model_Observer
{
    /**
     * Whether set quote to be persistent in workflow
     *
     * @var bool
     */
    protected $_setQuotePersistent = true;

    /**
     * Set persistent data to customer session
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Persistent_Model_Observer
     */
    public function emulateCustomer($observer)
    {
        if (!Mage::helper('persistent')->canProcess($observer)
            || !Mage::helper('enterprise_persistent')->isCustomerAndSegmentsPersist()
        ) {
            return $this;
        }

        if ($this->_isLoggedOut()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')->load(
                $this->_getPersistentHelper()->getSession()->getCustomerId()
            );
            Mage::getSingleton('customer/session')
                ->setCustomerId($customer->getId())
                ->setCustomerGroupId($customer->getGroupId());

            // apply persistent data to segments
            Mage::register('segment_customer', $customer, true);
            if ($this->_isWishlistPersist()) {
                Mage::helper('wishlist')->setCustomer($customer);
            }
        }
        return $this;
    }

    /**
     * Modify expired quotes cleanup
     *
     * @param Varien_Event_Observer $observer
     */
    public function modifyExpiredQuotesCleanup($observer)
    {
        /** @var $salesObserver Mage_Sales_Model_Observer */
        $salesObserver = $observer->getEvent()->getSalesObserver();
        $salesObserver->setExpireQuotesAdditionalFilterFields(array(
            'is_persistent' => 0
        ));
    }

    /**
     * Apply persistent data
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function applyPersistentData($observer)
    {
        if (!Mage::helper('persistent')->canProcess($observer)
            || !$this->_isPersistent() || Mage::getSingleton('customer/session')->isLoggedIn()
        ) {
            return;
        }
        Mage::getModel('persistent/persistent_config')
            ->setConfigFilePath(Mage::helper('enterprise_persistent')->getPersistentConfigFilePath())
            ->fire();
    }

    public function applyBlockPersistentData($observer)
    {
        $observer->getEvent()->setConfigFilePath(Mage::helper('enterprise_persistent')->getPersistentConfigFilePath());
        return Mage::getSingleton('persistent/observer')->applyBlockPersistentData($observer);
    }

    /**
     * Set whislist items count in top wishlist link block
     *
     * @deprecated after 1.11.2.0
     * @param Mage_Core_Block_Abstract $block
     * @return null
     */
    public function initWishlist($block)
    {
        if (!$this->_isWishlistPersist()) {
            return;
        }
        $block->setCustomWishlist($this->_initWishlist());
    }

    /**
     * Set persistent wishlist to wishlist sidebar block
     *
     * @deprecated after 1.11.2.0
     * @param Mage_Core_Block_Abstract $block
     * @return null
     */
    public function initWishlistSidebar($block)
    {
        if (!$this->_isWishlistPersist()) {
            return;
        }
        $block->setCustomWishlist($this->_initWishlist());
    }

    /**
     * Set persistent orders to recently orders block
     *
     * @param Mage_Core_Block_Abstract $block
     * @return null
     */
    public function initReorderSidebar($block)
    {
        if (!Mage::helper('enterprise_persistent')->isOrderedItemsPersist()) {
            return;
        }
        $block->setCustomerId($this->_getCustomerId());
        $block->initOrders();
    }

    /**
     * Emulate 'viewed products' block with persistent data
     *
     * @param Mage_Reports_Block_Product_Viewed $block
     * @return null
     */
    public function emulateViewedProductsBlock(Mage_Reports_Block_Product_Viewed $block)
    {
        if (!Mage::helper('enterprise_persistent')->isViewedProductsPersist()) {
            return;
        }
        $customerId = $this->_getCustomerId();
        $block->getModel()
            ->setCustomerId($customerId)
            ->calculate();
        $block->setCustomerId($customerId);
    }

    /**
     * Remove cart link
     *
     * @param Varien_Event_Observer $observer
     */
    public function removeCartLink($observer)
    {
        $block =  Mage::getSingleton('core/layout')->getBlock('checkout.links');
        if ($block) {
            $block->removeLinkByUrl(Mage::getUrl('checkout/cart'));
        }
    }

    /**
     * Emulate 'compared products' block with persistent data
     *
     * @param Mage_Reports_Block_Product_Compared $block
     * @return null
     */
    public function emulateComparedProductsBlock(Mage_Reports_Block_Product_Compared $block)
    {
        if (!$this->_isComparedProductsPersist()) {
            return;
        }
        $customerId = $this->_getCustomerId();
        $block->setCustomerId($customerId);
        $block->getModel()
            ->setCustomerId($customerId)
            ->calculate();
    }

    /**
     * Emulate 'compare products' block with persistent data
     *
     * @param Mage_Catalog_Block_Product_Compare_Sidebar $block
     * @return null
     */
    public function emulateCompareProductsBlock(Mage_Catalog_Block_Product_Compare_Sidebar $block)
    {
        if (!$this->_isCompareProductsPersist()) {
            return;
        }
        $collection = $block->getCompareProductHelper()
            ->setCustomerId($this->_getCustomerId())
            ->getItemCollection();
        $block->setItems($collection);
    }

    /**
     * Emulate 'compare products list' block with persistent data
     *
     * @param Mage_Catalog_Block_Product_Compare_List $block
     * @return null
     */
    public function emulateCompareProductsListBlock(Mage_Catalog_Block_Product_Compare_List $block)
    {
        if (!$this->_isCompareProductsPersist()) {
            return;
        }
        $block->setCustomerId($this->_getCustomerId());
    }

    /**
     * Apply persistent customer id
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function applyCustomerId($observer)
    {
        if (!Mage::helper('persistent')->canProcess($observer) || !$this->_isCompareProductsPersist()) {
            return;
        }
        $instance = $observer->getEvent()->getControllerAction();
        $instance->setCustomerId($this->_getCustomerId());
    }

    /**
     * Emulate customer wishlist (add, delete, etc)
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function emulateWishlist($observer)
    {
        if (!Mage::helper('persistent')->canProcess($observer)
            || !$this->_isPersistent() || !$this->_isWishlistPersist()
        ) {
            return;
        }

        $controller = $observer->getEvent()->getControllerAction();
        if ($controller instanceof Mage_Wishlist_IndexController) {
            $controller->skipAuthentication();
        }
    }

    /**
     * Set persistent data into quote
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function setQuotePersistentData($observer)
    {
        if (!Mage::helper('persistent')->canProcess($observer) || !$this->_isPersistent()) {
            return;
        }

        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        /** @var $customerSession Mage_Customer_Model_Session */
        $customerSession = Mage::getSingleton('customer/session');

        if (Mage::helper('enterprise_persistent')->isCustomerAndSegmentsPersist() && $this->_setQuotePersistent) {
            $customerId = $customerSession->getCustomerId();
            if ($customerId) {
                $quote->setCustomerId($customerId);
            }
            $customerGroupId = $customerSession->getCustomerGroupId();
            if ($customerGroupId) {
                $quote->setCustomerGroupId($customerGroupId);
            }
        }
    }

    /**
     * Prevent setting persistent data into quote
     *
     * @param  $observer
     * @see Enterprise_Persistent_Model_Observer::setQuotePersistentData
     */
    public function preventSettingQuotePersistent($observer)
    {
        $this->_setQuotePersistent = false;
    }

    /**
     * Update Option "Persist Customer Group Membership and Segmentation"
     * set value "Yes" if option "Persist Shopping Cart" equals "Yes"
     *
     * @param  $observer Enterprise_Persistent_Model_Observer
     * @return void
     */
    public function updateOptionCustomerSegmentation($observer)
    {
        $eventDataObject = $observer->getEvent()->getDataObject();

        if ($eventDataObject->getValue()) {
            $optionCustomerSegm = Mage::getModel('core/config_data')
                ->setScope($eventDataObject->getScope())
                ->setScopeId($eventDataObject->getScopeId())
                ->setPath(Enterprise_Persistent_Helper_Data::XML_PATH_PERSIST_CUSTOMER_AND_SEGM)
                ->setValue(true)
                ->save();
        }
    }

    /**
     * Expire data of Sidebars
     *
     * @param Varien_Event_Observer $observer
     */
    public function expireSidebars($observer)
    {
        $this->_expireCompareProducts();
        $this->_expireComparedProducts();
        $this->_expireViewedProducts();
    }

    /**
     * Expire data of Compare products sidebar
     *
     */
    public function _expireCompareProducts()
    {
        if (!$this->_isCompareProductsPersist()) {
            return;
        }
        Mage::getSingleton('catalog/product_compare_item')->bindCustomerLogout();
    }

    /**
     * Expire data of Compared products sidebar
     *
     */
    public function _expireComparedProducts()
    {
        if (!$this->_isComparedProductsPersist()) {
            return;
        }
        Mage::getModel('reports/product_index_compared')
            ->purgeVisitorByCustomer()
            ->calculate();
    }

    /**
     * Expire data of Viewed products sidebar
     *
     */
    public function _expireViewedProducts()
    {
        if (!$this->_isComparedProductsPersist()) {
            return;
        }
        Mage::getModel('reports/product_index_viewed')
            ->purgeVisitorByCustomer()
            ->calculate();
    }

    /**
     * Return persistent customer id
     *
     * @return int
     */
    protected function _getCustomerId()
    {
        return $this->_getPersistentHelper()->getSession()->getCustomerId();
    }

    /**
     * Retrieve persistent helper
     *
     * @return Mage_Persistent_Helper_Session
     */
    protected function _getPersistentHelper()
    {
        return Mage::helper('persistent/session');
    }

    /**
     * Init persistent wishlist
     *
     * @return Mage_Wishlist_Model_Wishlist
     */
    protected function _initWishlist()
    {
        return Mage::getModel('wishlist/wishlist')->loadByCustomer($this->_getCustomerId() ,true);
    }

    /**
     * Check whether wishlist is persist
     *
     * @return bool
     */
    protected function _isWishlistPersist()
    {
        return Mage::helper('enterprise_persistent')->isWishlistPersist();
    }

    /**
     * Check whether compare products is persist
     *
     * @return bool
     */
    protected function _isCompareProductsPersist()
    {
        return Mage::helper('enterprise_persistent')->isCompareProductsPersist();
    }

    /**
     * Check whether compared products is persist
     *
     * @return bool
     */
    protected function _isComparedProductsPersist()
    {
        return Mage::helper('enterprise_persistent')->isComparedProductsPersist();
    }

    /**
     * Check whether persistent mode is running
     *
     * @return bool
     */
    protected function _isPersistent()
    {
        return $this->_getPersistentHelper()->isPersistent();
    }

    /**
     * Check if persistent mode is running and customer is logged out
     *
     * @return bool
     */
    protected function _isLoggedOut()
    {
        return $this->_isPersistent() && !Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Check if shopping cart is guest while persistent session and user is logged out
     *
     * @return bool
     */
    protected function _isGuestShoppingCart()
    {
        return $this->_isLoggedOut() && !Mage::helper('persistent')->isShoppingCartPersist();
    }

    /**
     * Skip website restriction and allow access for persistent customers
     *
     * @param Varien_Event_Observer $observer
     */
    public function skipWebsiteRestriction(Varien_Event_Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        if ($result->getShouldProceed() && $this->_isPersistent()) {
            $result->setCustomerLoggedIn(true);
        }
    }
}
