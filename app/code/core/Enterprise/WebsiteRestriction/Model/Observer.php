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
 * @package     Enterprise_WebsiteRestriction
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Private sales and stubs observer
 *
 */
class Enterprise_WebsiteRestriction_Model_Observer
{
    /**
     * Implement website stub or private sales restriction
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictWebsite($observer)
    {
        /* @var $controller Mage_Core_Controller_Front_Action */
        $controller = $observer->getEvent()->getControllerAction();

        if (!Mage::app()->getStore()->isAdmin()) {
            $dispatchResult = new Varien_Object(array('should_proceed' => true, 'customer_logged_in' => false));
            Mage::dispatchEvent('websiterestriction_frontend', array(
                'controller' => $controller, 'result' => $dispatchResult
            ));
            if (!$dispatchResult->getShouldProceed()) {
                return;
            }
            if (!Mage::helper('enterprise_websiterestriction')->getIsRestrictionEnabled()) {
                return;
            }
            /* @var $request Mage_Core_Controller_Request_Http */
            $request    = $controller->getRequest();
            /* @var $response Mage_Core_Controller_Response_Http */
            $response   = $controller->getResponse();
            switch ((int)Mage::getStoreConfig(Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_MODE)) {
                // show only landing page with 503 or 200 code
                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_NONE:
                    if ($controller->getFullActionName() !== 'restriction_index_stub') {
                        $request->setModuleName('restriction')
                            ->setControllerName('index')
                            ->setActionName('stub')
                            ->setDispatched(false);
                        return;
                    }
                    $httpStatus = (int)Mage::getStoreConfig(
                        Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_HTTP_STATUS
                    );
                    if (Enterprise_WebsiteRestriction_Model_Mode::HTTP_503 === $httpStatus) {
                        $response->setHeader('HTTP/1.1','503 Service Unavailable');
                    }
                    break;

                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER:
                    // break intentionally omitted

                // redirect to landing page/login
                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_LOGIN:
                    if (!$dispatchResult->getCustomerLoggedIn() && !Mage::helper('customer')->isLoggedIn()) {
                        // see whether redirect is required and where
                        $redirectUrl = false;
                        $allowedActionNames = array_keys(Mage::getConfig()
                            ->getNode(Enterprise_WebsiteRestriction_Helper_Data::XML_NODE_RESTRICTION_ALLOWED_GENERIC)
                            ->asArray()
                        );
                        if (Mage::helper('customer')->isRegistrationAllowed()) {
                            foreach(array_keys(Mage::getConfig()
                                ->getNode(
                                    Enterprise_WebsiteRestriction_Helper_Data::XML_NODE_RESTRICTION_ALLOWED_REGISTER
                                )
                                ->asArray()) as $fullActionName
                            ) {
                                $allowedActionNames[] = $fullActionName;
                            }
                        }

                        // to specified landing page
                        $restrictionRedirectCode = (int)Mage::getStoreConfig(
                            Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_HTTP_REDIRECT
                        );
                        if (Enterprise_WebsiteRestriction_Model_Mode::HTTP_302_LANDING === $restrictionRedirectCode) {
                            $cmsPageViewAction = 'cms_page_view';
                            $allowedActionNames[] = $cmsPageViewAction;
                            $pageIdentifier = Mage::getStoreConfig(
                                Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_LANDING_PAGE
                            );
                            // Restrict access to CMS pages too
                            if (!in_array($controller->getFullActionName(), $allowedActionNames)
                                || ($controller->getFullActionName() === $cmsPageViewAction
                                    && $request->getAlias('rewrite_request_path') !== $pageIdentifier)
                            ) {
                                $redirectUrl = Mage::getUrl('', array('_direct' => $pageIdentifier));
                            }
                        } elseif (!in_array($controller->getFullActionName(), $allowedActionNames)) {
                            // to login form
                            $redirectUrl = Mage::getUrl('customer/account/login');
                        }

                        if ($redirectUrl) {
                            $response->setRedirect($redirectUrl);
                            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        }
                        if (Mage::getStoreConfigFlag(
                            Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                        )) {
                            $afterLoginUrl = Mage::helper('customer')->getDashboardUrl();
                        } else {
                            $afterLoginUrl = Mage::getUrl();
                        }
                        Mage::getSingleton('core/session')->setWebsiteRestrictionAfterLoginUrl($afterLoginUrl);
                    } elseif (Mage::getSingleton('core/session')->hasWebsiteRestrictionAfterLoginUrl()) {
                        $response->setRedirect(
                            Mage::getSingleton('core/session')->getWebsiteRestrictionAfterLoginUrl(true)
                        );
                        $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                    }
                    break;
            }
        }
    }

    /**
     * Attempt to disallow customers registration
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictCustomersRegistration($observer)
    {
        $result = $observer->getEvent()->getResult();
        if ((!Mage::app()->getStore()->isAdmin()) && $result->getIsAllowed()) {
            $restrictionMode = (int)Mage::getStoreConfig(
                Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_MODE
            );
            $result->setIsAllowed((!Mage::helper('enterprise_websiterestriction')->getIsRestrictionEnabled())
                || (Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER === $restrictionMode)
            );
        }
    }

    /**
     * Make layout load additional handler when in private sales mode
     *
     * @param Varien_Event_Observer $observer
     */
    public function addPrivateSalesLayoutUpdate($observer)
    {
        if (in_array((int)Mage::getStoreConfig(Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_MODE),
            array(
                Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER,
                Enterprise_WebsiteRestriction_Model_Mode::ALLOW_LOGIN
            ),
            true
        )) {
            $observer->getEvent()->getLayout()->getUpdate()->addHandle('restriction_privatesales_mode');
        }
    }
}
