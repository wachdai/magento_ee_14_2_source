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
 * @package     Enterprise_Banner
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Banner_Adminhtml_BannerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Banners list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('CMS'))->_title($this->__('Banners'));

        $this->loadLayout();
        $this->_setActiveMenu('cms/enterprise_banner');
        $this->renderLayout();
    }

    /**
     * Create new banner
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit action
     *
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_initBanner('id');

        if (!$model->getId() && $id) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('enterprise_banner')->__('This banner no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Banner'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('cms/enterprise_banner');
        $breadcrumbMessage = $id ? Mage::helper('enterprise_banner')->__('Edit Banner')
            : Mage::helper('enterprise_banner')->__('New Banner');
        $this->_addBreadcrumb($breadcrumbMessage, $breadcrumbMessage)
             ->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('id');
            $model = $this->_initBanner();
            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_banner')->__('This banner no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }

            //Filter disallowed data
            $currentStores = array_keys(Mage::app()->getStores(true));
            if (isset($data['store_contents_not_use'])) {
                $data['store_contents_not_use'] = array_intersect($data['store_contents_not_use'], $currentStores);
            }
            if (isset($data['store_contents'])) {
                $data['store_contents'] = array_intersect_key($data['store_contents'], array_flip($currentStores));
            }

            // prepare post data
            if (isset($data['banner_catalog_rules'])) {
                $related = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['banner_catalog_rules']);
                foreach ($related as $_key => $_rid) {
                    $related[$_key] = (int)$_rid;
                }
                $data['banner_catalog_rules'] = $related;
            }
            if (isset($data['banner_sales_rules'])) {
                $related = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['banner_sales_rules']);
                foreach ($related as $_key => $_rid) {
                    $related[$_key] = (int)$_rid;
                }
                $data['banner_sales_rules'] = $related;
            }

            if (!isset($data['customer_segment_ids'])) {
                $data['customer_segment_ids'] = array();
            }

            // save model
            try {
                if (!empty($data)) {
                    $model->addData($data);
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                }
                $model->save();
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_banner')->__('The banner has been saved.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('enterprise_banner')->__('Unable to save the banner.'));
                $redirectBack = true;
                Mage::logException($e);
            }
            if ($redirectBack) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     *
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                // init model and delete
                $model = Mage::getModel('enterprise_banner/banner');
                $model->load($id);
                $model->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_banner')->__('The banner has been deleted.')
                );
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_banner')->__('An error occurred while deleting banner data. Please review log and try again.')
                );
                Mage::logException($e);
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('enterprise_banner')->__('Unable to find a banner to delete.')
        );
        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Delete specified banners using grid massaction
     *
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('banner');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select banner(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getSingleton('enterprise_banner/banner')->load($id);
                    $model->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_banner')->__('An error occurred while mass deleting banners. Please review log and try again.')
                );
                Mage::logException($e);
                return;
            }
        }
        $this->_redirect('*/*/index');
    }


    /**
     * Load Banner from request
     *
     * @param string $idFieldName
     * @return Enterprise_Banner_Model_Banner $model
     */
    protected function _initBanner($idFieldName = 'banner_id')
    {
        $this->_title($this->__('CMS'))->_title($this->__('Banners'));

        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = Mage::getModel('enterprise_banner/banner');
        if ($id) {
            $model->load($id);
        }
        if (!Mage::registry('current_banner')) {
            Mage::register('current_banner', $model);
        }
        return $model;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/enterprise_banner');
    }

    /**
     * Render Banner grid
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Banner sales rule grid action on promotions tab
     * Load banner by ID from post data
     * Register banner model
     *
     */
    public function salesRuleGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_initBanner('id');

        if (!$model->getId() && $id) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('enterprise_banner')->__('This banner no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $this->loadLayout();
        $this->getLayout()
            ->getBlock('banner_salesrule_grid')
            ->setSelectedSalesRules($this->getRequest()->getPost('selected_salesrules'));
        $this->renderLayout();
    }

    /**
     * Banner catalog rule grid action on promotions tab
     * Load banner by ID from post data
     * Register banner model
     *
     */
    public function catalogRuleGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_initBanner('id');

        if (!$model->getId() && $id) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('enterprise_banner')->__('This banner no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $this->loadLayout();
        $this->getLayout()
            ->getBlock('banner_catalogrule_grid')
            ->setSelectedCatalogRules($this->getRequest()->getPost('selected_catalogrules'));
        $this->renderLayout();
    }

    /**
     * Banner binding tab grid action on sales rule
     *
     */
    public function salesRuleBannersGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('salesrule/rule');

        if ($id) {
            $model->load($id);
            if (! $model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('salesrule')->__('This rule no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }
        if (!Mage::registry('current_promo_quote_rule')) {
            Mage::register('current_promo_quote_rule', $model);
        }
        $this->loadLayout();
        $this->getLayout()
            ->getBlock('related_salesrule_banners_grid')
            ->setSelectedSalesruleBanners($this->getRequest()->getPost('selected_salesrule_banners'));
        $this->renderLayout();
    }

   /**
     * Banner binding tab grid action on catalog rule
     *
     */
    public function catalogRuleBannersGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('catalogrule/rule');

        if ($id) {
            $model->load($id);
            if (! $model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('catalogrule')->__('This rule no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }
        if (!Mage::registry('current_promo_catalog_rule')) {
            Mage::register('current_promo_catalog_rule', $model);
        }
        $this->loadLayout();
        $this->getLayout()
            ->getBlock('related_catalogrule_banners_grid')
            ->setSelectedCatalogruleBanners($this->getRequest()->getPost('selected_catalogrule_banners'));
        $this->renderLayout();
    }
}
