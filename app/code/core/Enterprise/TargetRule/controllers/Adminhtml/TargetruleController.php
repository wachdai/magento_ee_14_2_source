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
 * @package     Enterprise_TargetRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_TargetRule_Adminhtml_TargetRuleController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initial actions
     *
     * @return unknown
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('catalog/targetrule');
        return $this;
    }

    /**
     * Index grid
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Rule-based Product Relations'));

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Grid ajax action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create new target rule
     *
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit action
     *
     */
    public function editAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Rule-based Product Relations'));

        /* @var $model Enterprise_TargetRule_Model_Rule */
        $model  = Mage::getModel('enterprise_targetrule/rule');
        $ruleId = $this->getRequest()->getParam('id', null);

        if ($ruleId) {
            $model->load($ruleId);
            if (!$model->getId()) {
                $this->_getSession()->addError(Mage::helper('enterprise_targetrule')->__('This rule no longer exists'));
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Rule'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('current_target_rule', $model);

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Ajax conditions
     *
     */
    public function newConditionHtmlAction()
    {
        $this->conditionsHtmlAction('conditions');
    }

    public function newActionsHtmlAction()
    {
        $this->conditionsHtmlAction('actions');
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        $redirectPath   = '*/*/';
        $redirectParams = array();

        $data = $this->getRequest()->getPost();

        if ($this->getRequest()->isPost() && $data) {
            /* @var $model Enterprise_TargetRule_Model_Rule */
            $model          = Mage::getModel('enterprise_targetrule/rule');
            $redirectBack   = $this->getRequest()->getParam('back', false);
            $hasError       = false;

            try {
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                $ruleId = $this->getRequest()->getParam('rule_id');
                if ($ruleId) {
                    $model->load($ruleId);
                    if ($ruleId != $model->getId()) {
                        Mage::throwException(Mage::helper('enterprise_targetrule')->__('Wrong rule specified.'));
                    }
                }

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setFormData($data);

                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                $data['actions']    = $data['rule']['actions'];
                unset($data['rule']);

                $model->loadPost($data);
                $model->save();
                Mage::app()->getCacheInstance()->invalidateType('block_html');

                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_targetrule')->__('The rule has been saved.')
                );

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'       => $model->getId(),
                        '_current' => true,
                    ));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $hasError = true;
            } catch (Zend_Date_Exception $e) {
                $this->_getSession()->addError(Mage::helper('enterprise_targetrule')->__('Invalid date.'));
                $hasError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('enterprise_targetrule')->__('An error occurred while saving Product Rule.')
                );

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }

            if ($hasError) {
                $this->_getSession()->setFormData($data);
            }

            if ($hasError || $redirectBack) {
                $redirectPath = '*/*/edit';
                $redirectParams['id'] = $model->getId();
            }
        }
        $this->_redirect($redirectPath, $redirectParams);
    }

    /**
     * Delete target rule
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('enterprise_targetrule/rule');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('enterprise_targetrule')->__('The rule has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')
            ->addError(Mage::helper('enterprise_targetrule')->__('Unable to find a page to delete'));
        $this->_redirect('*/*/');
    }

    /**
     * Generate elements for condition forms
     *
     * @param string $prefix Form prefix
     */
    protected function conditionsHtmlAction($prefix)
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('enterprise_targetrule/rule'))
            ->setPrefix($prefix);
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Check is allowed access to targeted product rules management
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/targetrule');
    }
}
