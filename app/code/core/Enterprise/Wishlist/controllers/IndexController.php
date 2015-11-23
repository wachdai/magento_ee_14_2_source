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
 * @package     Enterprise_Wishlist
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Multiple wishlist frontend search controller
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */

require_once 'Mage/Wishlist/controllers/IndexController.php';

class Enterprise_Wishlist_IndexController extends Mage_Wishlist_IndexController
{
    /**
     * Check if multiple wishlist is enabled on current store before all other actions
     *
     * @return Enterprise_Wishlist_IndexController
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        $protectedActions = array(
            'createwishlist', 'editwishlist', 'deletewishlist', 'copyitems', 'moveitem', 'moveitems'
        );
        if (!Mage::helper('enterprise_wishlist')->isMultipleEnabled() && in_array($action, $protectedActions)) {
            $this->norouteAction();
        }

        return $this;
    }

    /**
     * Retrieve customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Add item to wishlist
     * Create new wishlist if wishlist params (name, visibility) are provided
     */
    public function addAction()
    {
        $customerId = $this->_getSession()->getCustomerId();
        $name = $this->getRequest()->getParam('name');
        $visibility = ($this->getRequest()->getParam('visibility', 0) === 'on' ? 1 : 0);
        if ($name !== null) {
            try {
                $wishlist = $this->_editWishlist($customerId, $name, $visibility);
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_wishlist')->__('Wishlist "%s" was successfully saved', Mage::helper('core')->escapeHtml($wishlist->getName()))
                );
                $this->getRequest()->setParam('wishlist_id', $wishlist->getId());
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('enterprise_wishlist')->__('Error happened during wishlist creation')
                );
            }
        }
        parent::addAction();
    }

    /**
     * Display customer wishlist
     */
    public function indexAction()
    {
        /* @var $helper Enterprise_Wishlist_Helper_Data */
        $helper = Mage::helper('enterprise_wishlist');
        if (!$helper->isMultipleEnabled() ) {
            $wishlistId = $this->getRequest()->getParam('wishlist_id');
            if ($wishlistId && $wishlistId != $helper->getDefaultWishlist()->getId() ) {
                $this->_redirectUrl($helper->getListUrl());
            }
        }
        parent::indexAction();
    }

    /**
     * Create new customer wishlist
     */
    public function createwishlistAction()
    {
        $this->_forward('editwishlist');
    }

    /**
     * Edit wishlist
     *
     * @param int $customerId
     * @param string $wishlistName
     * @param bool $visibility
     * @param int $wishlistId
     * @return Mage_Wishlist_Model_Wishlist
     */
    protected function _editWishlist($customerId, $wishlistName, $visibility = false, $wishlistId = null)
    {
        $wishlist = Mage::getModel('wishlist/wishlist');

        if (!$customerId) {
            Mage::throwException(Mage::helper('enterprise_wishlist')->__('Log in to edit wishlists.'));
        }
        if (!strlen($wishlistName)) {
            Mage::throwException(Mage::helper('enterprise_wishlist')->__('Provide wishlist name'));
        }
        if ($wishlistId){
            $wishlist->load($wishlistId);
            if ($wishlist->getCustomerId() !== $this->_getSession()->getCustomerId()) {
                Mage::throwException(
                    Mage::helper('enterprise_wishlist')->__('The wishlist is not assigned to your account and cannot be edited ')
                );
            }
        } else {
            $wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection()
                ->filterByCustomerId($customerId);
            $limit = Mage::helper('enterprise_wishlist')->getWishlistLimit();
            if (Mage::helper('enterprise_wishlist')->isWishlistLimitReached($wishlistCollection)) {
                Mage::throwException(
                    Mage::helper('enterprise_wishlist')->__('Only %d wishlists can be created.', $limit)
                );
            }
            $wishlist->setCustomerId($customerId);
        }
        $wishlist->setName($wishlistName)
            ->setVisibility($visibility)
            ->generateSharingCode()
            ->save();
        return $wishlist;
    }

    /**
     * Edit wishlist properties
     *
     * @return Mage_Core_Controller_Varien_Action|Zend_Controller_Response_Abstract
     */
    public function editwishlistAction()
    {
        $customerId = $this->_getSession()->getCustomerId();
        $wishlistName = $this->getRequest()->getParam('name');
        $visibility = ($this->getRequest()->getParam('visibility', 0) === 'on' ? 1 : 0);
        $wishlistId = $this->getRequest()->getParam('wishlist_id');
        $wishlist = null;
        try {
            $wishlist = $this->_editWishlist($customerId, $wishlistName, $visibility, $wishlistId);

            $this->_getSession()->addSuccess(
                Mage::helper('enterprise_wishlist')->__('Wishlist "%s" was successfully saved', Mage::helper('core')->escapeHtml($wishlist->getName()))
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('enterprise_wishlist')->__('Error happened during wishlist creation')
            );
        }

        if (!$wishlist || !$wishlist->getId()) {
            $this->_getSession()->addError('Could not create wishlist');
        }

        if ($this->getRequest()->isAjax()) {
            $this->getResponse()->setHeader('Content-Type', 'application/json');
            $params = array();
            if (!$wishlist->getId()) {
                $params = array('redirect' => Mage::getUrl('*/*'));
            } else {
                $params = array('wishlist_id' => $wishlist->getId());
            }
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($params));
        } else {
            if (!$wishlist || !$wishlist->getId()) {
                return $this->_redirect('*/*');
            } else {
                $this->_redirect('wishlist/index/index', array('wishlist_id' => $wishlist->getId()));
            }
        }
    }

    /**
     * Delete wishlist by id
     *
     * @return void
     */
    public function deletewishlistAction()
    {
        try {
            $wishlist = $this->_getWishlist();
            if (!$wishlist) {
                return $this->norouteAction();
            }
            if (Mage::helper('enterprise_wishlist')->isWishlistDefault($wishlist)) {
                Mage::throwException(
                    Mage::helper('enterprise_wishlist')->__('Default wishlist cannot be deleted')
                );
            }
            $wishlist->delete();
            Mage::helper('wishlist')->calculate();
            Mage::getSingleton('wishlist/session')->addSuccess(
                Mage::helper('enterprise_wishlist')->__('Wishlist "%s" has been deleted.', Mage::helper('core')->escapeHtml($wishlist->getName()))
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $message = Mage::helper('enterprise_wishlist')->__('An error occurred during wishlist deletion.');
            $this->_getSession()->addException($e, $message);
        }
    }

    /**
     * Build wishlist product name list string
     *
     * @param array $items
     * @return string
     */
    protected function _joinProductNames($items)
    {
        $names = array();
        foreach ($items as $item) {
            $names[] = '"' . $item->getProduct()->getName() . '"';
        }
        return join(', ', $names);
    }

    /**
     * Copy item to given wishlist
     *
     * @param Mage_Wishlist_Model_Item $item
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @param int $qty
     * @throws InvalidArgumentException|DomainException
     */
    protected function _copyItem(Mage_Wishlist_Model_Item $item, Mage_Wishlist_Model_Wishlist $wishlist, $qty = null)
    {
        if (!$item->getId()) {
            throw new InvalidArgumentException();
        }
        if ($item->getWishlistId() == $wishlist->getId()) {
            throw new DomainException();
        }
        $buyRequest = $item->getBuyRequest();
        if ($qty) {
            $buyRequest->setQty($qty);
        }
        $wishlist->addNewItem($item->getProduct(), $buyRequest);
        Mage::dispatchEvent(
            'wishlist_add_product',
            array(
                'wishlist'  => $wishlist,
                'product'   => $item->getProduct(),
                'item'      => $item
            )
        );
    }

    /**
     * Copy wishlist item to given wishlist
     *
     * @return void
     */
    public function copyitemAction()
    {
        $session = $this->_getSession();
        $requestParams = $this->getRequest()->getParams();
        if ($session->getBeforeWishlistRequest()) {
            $requestParams = $session->getBeforeWishlistRequest();
            $session->unsBeforeWishlistRequest();
        }

        $wishlist = $this->_getWishlist(isset($requestParams['wishlist_id']) ? $requestParams['wishlist_id'] : null);
        if (!$wishlist) {
            return $this->norouteAction();
        }
        $itemId = isset($requestParams['item_id']) ? $requestParams['item_id'] : null;
        $qty = isset($requestParams['qty']) ? $requestParams['qty'] : null;
        if ($itemId) {
            $productName = '';
            try {
                /* @var Mage_Wishlist_Model_Item $item */
                $item = Mage::getModel('wishlist/item');
                $item->loadWithOptions($itemId);

                $wishlistName = Mage::helper('core')->escapeHtml($wishlist->getName());
                $productName = Mage::helper('core')->escapeHtml($item->getProduct()->getName());

                $this->_copyItem($item, $wishlist, $qty);
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_wishlist')->__('"%s" was successfully copied to %s.', $productName, $wishlistName)
                );
                Mage::helper('wishlist')->calculate();
            } catch (InvalidArgumentException $e) {
                $this->_getSession->addError(
                    Mage::helper('enterprise_wishlist')->__('Item was not found.')
                );
            } catch (DomainException $e) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_wishlist')->__('"%s" is already present in %s.', $productName, $wishlistName)
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                if ($productName) {
                    $message = Mage::helper('enterprise_wishlist')->__('Could not copy "%s".', $productName);
                } else {
                    $message = Mage::helper('enterprise_wishlist')->__('Could not copy wishlist item.');
                }
                $this->_getSession()->addError($message);
            }
        }
        $wishlist->save();
        if ($this->_getSession()->hasBeforeWishlistUrl())
        {
            $this->_redirectUrl($this->_getSession()->getBeforeWishlistUrl());
            $this->_getSession()->unsBeforeWishlistUrl();
        } else {
            $this->_redirectReferer();
        }
    }

    /**
     * Copy wishlist items to given wishlist
     *
     * @return void
     */
    public function copyitemsAction()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }
        $itemIds = $this->getRequest()->getParam('selected', array());
        $notFound = array();
        $alreadyPresent = array();
        $failed = array();
        $copied = array();
        if (count($itemIds)) {
            $qtys = $this->getRequest()->getParam('qty', array());
            foreach ($itemIds as $id => $value) {
                try {
                    /* @var Mage_Wishlist_Model_Item $item */
                    $item = Mage::getModel('wishlist/item');
                    $item->loadWithOptions($id);

                    $this->_copyItem($item, $wishlist, isset($qtys[$id]) ? $qtys[$id] : null);
                    $copied[$id] = $item;
                } catch (InvalidArgumentException $e) {
                    $notFound[] = $id;
                } catch (DomainException $e) {
                    $alreadyPresent[$id] = $item;
                } catch (Exception $e) {
                    Mage::logException($e);
                    $failed[] = $id;
                }
            }
        }
        $wishlistName = Mage::helper('core')->escapeHtml($wishlist->getName());

        $wishlist->save();

        if (count($notFound)) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_wishlist')->__('%d items were not found.', count($notFound))
            );
        }

        if (count($failed)) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_wishlist')->__('%d items could not be copied.', count($failed))
            );
        }

        if (count($alreadyPresent)) {
            $names = Mage::helper('core')->escapeHtml($this->_joinProductNames($alreadyPresent));
            $this->_getSession()->addError(
                Mage::helper('enterprise_wishlist')->__('%d items are already present in %s: %s.', count($alreadyPresent), $wishlistName, $names)
            );
        }

        if (count($copied)) {
            Mage::helper('wishlist')->calculate();
            $names = Mage::helper('core')->escapeHtml($this->_joinProductNames($copied));
            $this->_getSession()->addSuccess(
                Mage::helper('enterprise_wishlist')->__('%d items were copied to %s: %s.', count($copied), $wishlistName, $names)
            );
        }
        $this->_redirectReferer();
    }

    /**
     * Move item to given wishlist.
     * Check whether item belongs to one of customer's wishlists
     *
     * @param Mage_Wishlist_Model_Item $item
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @param Mage_Wishlist_Model_Resource_Wishlist_Collection $customerWishlists
     * @param int $qty
     * @throws InvalidArgumentException|DomainException
     */
    protected function _moveItem(
        Mage_Wishlist_Model_Item $item,
        Mage_Wishlist_Model_Wishlist $wishlist,
        Mage_Wishlist_Model_Resource_Wishlist_Collection $customerWishlists,
        $qty = null
    ) {
        if (!$item->getId()) {
            throw new InvalidArgumentException();
        }
        if ($item->getWishlistId() == $wishlist->getId()) {
            throw new DomainException(null, 1);
        }
        if (!$customerWishlists->getItemById($item->getWishlistId())) {
            throw new DomainException(null, 2);
        }

        $buyRequest = $item->getBuyRequest();
        if ($qty) {
            $buyRequest->setQty($qty);
        }
        $wishlist->addNewItem($item->getProduct(), $buyRequest);
        $qtyDiff = $item->getQty() - $qty;
        if ($qty && $qtyDiff > 0) {
            $item->setQty($qtyDiff);
            $item->save();
        } else {
            $item->delete();
        }
    }

    /**
     * Move wishlist item to given wishlist
     *
     * @return void
     */
    public function moveitemAction()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }
        $itemId = $this->getRequest()->getParam('item_id');

        if ($itemId) {
            try {
                $wishlists = Mage::getModel('wishlist/wishlist')->getCollection()
                    ->filterByCustomerId($this->_getSession()->getCustomerId());

                /* @var Mage_Wishlist_Model_Item $item */
                $item = Mage::getModel('wishlist/item');
                $item->loadWithOptions($itemId);

                $productName = Mage::helper('core')->escapeHtml($item->getProduct()->getName());
                $wishlistName = Mage::helper('core')->escapeHtml($wishlist->getName());

                $this->_moveItem($item, $wishlist, $wishlists, $this->getRequest()->getParam('qty', null));
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_wishlist')->__('"%s" was successfully moved to %s.', $productName, $wishlistName)
                );
                Mage::helper('wishlist')->calculate();
            } catch (InvalidArgumentException $e) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_wishlist')->__("Item with specified ID doesn't exist.")
                );
            } catch (DomainException $e) {
                if ($e->getCode() == 1) {
                    $this->_getSession()->addError(
                        Mage::helper('enterprise_wishlist')->__('"%s" is already present in %s.', $productName, $wishlistName)
                    );
                } else {
                    $this->_getSession()->addError(
                        Mage::helper('enterprise_wishlist')->__('"%s" cannot be moved.', $productName)
                    );
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('enterprise_wishlist')->__('Could not move wishlist item.')
                );
            }
        }
        $wishlist->save();
        $this->_redirectReferer();
    }

    /**
     * Move wishlist items to given wishlist
     */
    public function moveitemsAction()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }
        $itemIds = $this->getRequest()->getParam('selected', array());
        $moved = array();
        $failed = array();
        $notFound = array();
        $notAllowed = array();
        $alreadyPresent = array();
        if (count($itemIds)) {
            $wishlists = Mage::getModel('wishlist/wishlist')->getCollection();
            $wishlists->filterByCustomerId($this->_getSession()->getCustomerId());
            $qtys = $this->getRequest()->getParam('qty', array());

            foreach ($itemIds as $id => $value) {
                try {
                    /* @var Mage_Wishlist_Model_Item $item */
                    $item = Mage::getModel('wishlist/item');
                    $item->loadWithOptions($id);

                    $this->_moveItem($item, $wishlist, $wishlists, isset($qtys[$id]) ? $qtys[$id] : null);
                    $moved[$id] = $item;
                } catch (InvalidArgumentException $e) {
                    $notFound[] = $id;
                } catch (DomainException $e) {
                    if ($e->getCode() == 1) {
                        $alreadyPresent[$id] = $item;
                    } else {
                        $notAllowed[$id] = $item;
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                    $failed[] = $id;
                }
            }
        }

        $wishlistName = Mage::helper('core')->escapeHtml($wishlist->getName());

        if (count($notFound)) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_wishlist')->__('%d items were not found.', count($notFound))
            );
        }

        if (count($notAllowed)) {
            $names = Mage::helper('core')->escapeHtml($this->_joinProductNames($notAllowed));
            $this->_getSession()->addError(
                Mage::helper('enterprise_wishlist')->__('%d items cannot be moved: %s.', count($notAllowed), $names)
            );
        }

        if (count($alreadyPresent)) {
            $names = Mage::helper('core')->escapeHtml($this->_joinProductNames($alreadyPresent));
            $this->_getSession()->addError(
                Mage::helper('enterprise_wishlist')->__('%d items are already present in %s: %s.', count($alreadyPresent), $wishlistName, $names)
            );
        }

        if (count($failed)) {
            $this->_getSession()->addError(
                Mage::helper('enterprise_wishlist')->__('%d items could not be moved.', count($failed))
            );
        }

        if (count($moved)) {
            Mage::helper('wishlist')->calculate();
            $names = Mage::helper('core')->escapeHtml($this->_joinProductNames($moved));
            $this->_getSession()->addSuccess(
                Mage::helper('enterprise_wishlist')->__('%d items were successfully moved to %s: %s.', count($moved), $wishlistName, $names)
            );
        }
        $this->_redirectReferer();
    }
}
