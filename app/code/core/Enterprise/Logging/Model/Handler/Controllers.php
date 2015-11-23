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
 * @package     Enterprise_Logging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Handles generic and specific logic for logging on pre/postdispatch
 *
 * All action handlers may take the $config and $eventModel params, which are configuration node for current action and
 * the event model respectively
 *
 * Action will be logged only if the handler returns non-empty value
 *
 */
class Enterprise_Logging_Model_Handler_Controllers
{

    /**
     * Generic Action handler
     *
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchGeneric($config, $eventModel, $processorModel)
    {
        if ($collectedIds = $processorModel->getCollectedIds()) {
            $eventModel->setInfo(Mage::helper('enterprise_logging')->implodeValues($collectedIds));
            return true;
        }
        return false;
    }

    /*
     * Special postDispach handlers below
    */

    /**
     * Simply log action without any id-s
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return bool
     */
    public function postDispatchSimpleSave($config, $eventModel)
    {
        return true;
    }

    /**
     * Custom handler for config view
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchConfigView($config, $eventModel)
    {
        $id = Mage::app()->getRequest()->getParam('section');
        if (!$id) {
            $id = 'general';
        }
        $eventModel->setInfo($id);
        return true;
    }

    /**
     * Custom handler for config save
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @param Enterprise_Logging_Model_Processor $processor
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchConfigSave($config, $eventModel, $processor)
    {
        $request = Mage::app()->getRequest();
        $postData = $request->getPost();
        $groupFieldsData = array();
        $change = Mage::getModel('enterprise_logging/event_changes');

        //Collect skip encrypted fields
        $encryptedNodeEntriesPaths = Mage::getSingleton('adminhtml/config')->getEncryptedNodeEntriesPaths(true);
        $skipEncrypted = array();
        foreach ($encryptedNodeEntriesPaths as $fieldName) {
            $skipEncrypted[] = $fieldName['field'];
        }

        //For each group of current section creating separated event change
        if (isset($postData['groups'])) {
            foreach ($postData['groups'] as $groupName => $groupData) {
                foreach ($groupData['fields'] as $fieldName => $fieldValueData) {
                    //Clearing config data accordingly to collected skip fields
                    if (!in_array($fieldName, $skipEncrypted) && isset($fieldValueData['value'])) {
                        $groupFieldsData[$fieldName] = $fieldValueData['value'];
                    }
                }

                $processor->addEventChanges(
                    clone $change->setSourceName($groupName)
                                 ->setOriginalData(array())
                                 ->setResultData($groupFieldsData)
                );
                $groupFieldsData = array();
            }
        }
        $id = $request->getParam('section');
        if (!$id) {
            $id = 'general';
        }
        return $eventModel->setInfo($id);
    }

    /**
     * Custom handler for category move
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchCategoryMove($config, $eventModel)
    {
        return $eventModel->setInfo(Mage::app()->getRequest()->getParam('id'));
    }

    /**
     * Custom handler for global search
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchGlobalSearch($config, $eventModel)
    {
        return $eventModel->setInfo(Mage::app()->getRequest()->getParam('query'));
    }

    /**
     * Handler for forgotpassword
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchForgotPassword($config, $eventModel)
    {
        if (Mage::app()->getRequest()->isPost()) {
            if ($model = Mage::registry('enterprise_logging_saved_model_adminhtml_index_forgotpassword')) {
                $info = $model->getId();
            } else {
                $info = Mage::app()->getRequest()->getParam('email');
            }
            $success = true;
            $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
            if ($messages) {
                $success = 'error' != $messages->getType();
            }
            return $eventModel->setIsSuccess($success)->setInfo($info);
        }
        return false;
    }

    /**
     * Custom handler for poll save fail's action
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchPollValidation($config, $eventModel)
    {
        $out = json_decode(Mage::app()->getResponse()->getBody());
        if (!empty($out->error)) {
            $id = Mage::app()->getRequest()->getParam('id');
            return $eventModel->setIsSuccess(false)->setInfo($id == 0 ? '' : $id);
        } else {
            $poll = Mage::registry('current_poll_model');
            if ($poll && $poll->getId()) {
                return $eventModel->setIsSuccess(true)->setInfo($poll->getId());
            }
        }
        return false;
    }

    /**
     * Custom handler for customer validation fail's action
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchCustomerValidate($config, $eventModel) {
        $out = json_decode(Mage::app()->getResponse()->getBody());
        if (!empty($out->error)) {
            $id = Mage::app()->getRequest()->getParam('id');
            return $eventModel->setIsSuccess(false)->setInfo($id == 0 ? '' : $id);
        }
        return false;
    }

    /**
     * Handler for reports
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchReport($config, $eventModel, $processor)
    {
        $fullActionNameParts = explode('_report_', $config->getName(), 2);
        if (empty($fullActionNameParts[1])) {
            return false;
        }

        $request = Mage::app()->getRequest();
        $filter = $request->getParam('filter');

        //Filtering request data
        $data = array_intersect_key($request->getParams(), array(
            'report_from' => null,
            'report_to' => null,
            'report_period' => null,
            'store' => null,
            'website' => null,
            'group' => null
        ));

        //Need when in request data there are was no period info
        if ($filter) {
            $filterData = Mage::app()->getHelper('adminhtml')->prepareFilterString($filter);
            $data = array_merge($data, (array)$filterData);
        }

        //Add log entry details
        if ($data) {
            $change = Mage::getModel('enterprise_logging/event_changes');
            $processor->addEventChanges($change->setSourceName('params')
                ->setOriginalData(array())
                ->setResultData($data));
        }

        return $eventModel->setInfo($fullActionNameParts[1]);
    }

    /**
     * Custom handler for catalog price rules apply
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchPromoCatalogApply($config, $eventModel)
    {
        $request = Mage::app()->getRequest();
        return $eventModel->setInfo($request->getParam('rule_id') ? $request->getParam('rule_id') : 'all rules');
    }

    /**
     * Custom handler for catalog price rules save & apply
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @param Enterprise_Logging_Model_Processor $processorModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchPromoCatalogSaveAndApply($config, $eventModel, $processorModel)
    {
        $request = Mage::app()->getRequest();

        $this->postDispatchGeneric($config, $eventModel, $processorModel);
        if ($request->getParam('auto_apply')) {
            $eventModel->setInfo(Mage::helper('enterprise_logging')->__('%s & applied', $eventModel->getInfo()));
        }

        return $eventModel;
    }

    /**
     *
     * @deprecated after 1.6.1.0
     */
    public function postDispatchMyAccountSave($config, $eventModel){}

    /**
     * Special handler for newsletter unsubscribe
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchNewsletterUnsubscribe($config, $eventModel)
    {
        $id = Mage::app()->getRequest()->getParam('subscriber');
        if (is_array($id)) {
            $id = implode(', ', $id);
        }
        return $eventModel->setInfo($id);
    }

    /**
     * Custom tax import handler
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchTaxRatesImport($config, $eventModel)
    {
        if (!Mage::app()->getRequest()->isPost()) {
            return false;
        }
        $success = true;
        $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
        if ($messages) {
            $success = 'error' != $messages->getType();
        }
        return $eventModel->setIsSuccess($success)->setInfo(Mage::helper('enterprise_logging')->__('Tax Rates Import'));
    }

    /**
     *
     * @deprecated after 1.6.0.0-rc1
     */
    public function postDispatchSystemStoreSave($config, $eventModel){}

    /**
     * Custom handler for catalog product mass attribute update
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchProductUpdateAttributes($config, $eventModel, $processor)
    {
        $request = Mage::app()->getRequest();
        $change = Mage::getModel('enterprise_logging/event_changes');
        $products = $request->getParam('product');
        if (!$products) {
            $products = Mage::helper('adminhtml/catalog_product_edit_action_attribute')->getProductIds();
        }
        if ($products) {
            $processor->addEventChanges(clone $change->setSourceName('product')
                ->setOriginalData(array())
                ->setResultData(array('ids' => implode(', ', $products))));
        }

        $processor->addEventChanges(clone $change->setSourceName('inventory')
                ->setOriginalData(array())
                ->setResultData($request->getParam('inventory', array())));
        $attributes = $request->getParam('attributes', array());
        $status = $request->getParam('status', null);
        if (!$attributes && $status) {
            $attributes['status'] = $status;
        }
        $processor->addEventChanges(clone $change->setSourceName('attributes')
                ->setOriginalData(array())
                ->setResultData($attributes));

        $websiteIds = $request->getParam('remove_website', array());
        if ($websiteIds) {
            $processor->addEventChanges(clone $change->setSourceName('remove_website_ids')
                ->setOriginalData(array())
                ->setResultData(array('ids' => implode(', ', $websiteIds))));
        }

        $websiteIds = $request->getParam('add_website', array());
        if ($websiteIds) {
            $processor->addEventChanges(clone $change->setSourceName('add_website_ids')
                ->setOriginalData(array())
                ->setResultData(array('ids' => implode(', ', $websiteIds))));
        }

        return $eventModel->setInfo(Mage::helper('enterprise_logging')->__('Attributes Updated'));
    }

     /**
     * Custom switcher for tax_class_save, to distinguish product and customer tax classes
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchTaxClassSave($config, $eventModel)
    {
        if (!Mage::app()->getRequest()->isPost()) {
            return false;
        }
        $classType = Mage::app()->getRequest()->getParam('class_type');
        $classId = Mage::app()->getRequest()->getParam('class_id');
        if ($classType == 'PRODUCT') {
            $eventModel->setEventCode('tax_product_tax_classes');
        }
        $success = true;
        $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
        if ($messages) {
            $success = 'error' != $messages->getType();
        }
        return $eventModel->setIsSuccess($success)->setInfo($classType
            . ($classId ? ': #' . Mage::app()->getRequest()->getParam('class_id') : ''));
    }

    /**
     * Custom handler for customer segment match
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchCustomerSegmentMatch($config, $eventModel)
    {
        $request = Mage::app()->getRequest();
        $customersQty = Mage::getModel('enterprise_customersegment/segment')->getResource()
                ->getSegmentCustomersQty($request->getParam('id'));
        return $eventModel->setInfo(
            $request->getParam('id') ?
                Mage::helper('enterprise_customersegment')->__('Matched %d Customers of Segment %s', $customersQty, $request->getParam('id')) :
                '-'
        );
    }

    /**
     * Custom handler for creating System Backup
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchSystemBackupsCreate($config, $eventModel)
    {
        $backup = Mage::registry('backup_manager');

        if ($backup) {
            $eventModel->setIsSuccess($backup->getIsSuccess())
                ->setInfo($backup->getBackupFilename());

            $errorMessage = $backup->getErrorMessage();
            if (!empty($errorMessage)) {
                $eventModel->setErrorMessage($errorMessage);
            }
        } else {
            $eventModel->setIsSuccess(false);
        }
        return $eventModel;
    }

    /**
     * Custom handler for deleting System Backup
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchSystemBackupsDelete($config, $eventModel)
    {
        $backup = Mage::registry('backup_manager');

        if ($backup) {
            $eventModel->setIsSuccess($backup->getIsSuccess())
                ->setInfo(Mage::helper('enterprise_logging')->implodeValues($backup->getDeleteResult()));
        } else {
            $eventModel->setIsSuccess(false);
        }
        return $eventModel;
    }

    /**
     * Custom handler for creating System Rollback
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchSystemRollback($config, $eventModel)
    {
        $backup = Mage::registry('backup_manager');

        if ($backup) {
            $eventModel->setIsSuccess($backup->getIsSuccess())
                ->setInfo($backup->getBackupFilename());

            $errorMessage = $backup->getErrorMessage();
            if (!empty($errorMessage)) {
                $eventModel->setErrorMessage($errorMessage);
            }
        } else {
            $eventModel->setIsSuccess(false);
        }

        return $eventModel;
    }

    /**
     * Custom handler for mass unlocking locked admin users
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchAdminAccountsMassUnlock($config, $eventModel)
    {
        if (!Mage::app()->getRequest()->isPost()) {
            return false;
        }
        $userIds = Mage::app()->getRequest()->getPost('unlock', array());
        if (!is_array($userIds)) {
            $userIds = array();
        }
        if (!$userIds) {
            return false;
        }
        return $eventModel->setInfo(implode(', ', $userIds));
    }

    /**
     * Custom handler for mass reindex process on index management
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchReindexProcess($config, $eventModel)
    {
        $processIds = Mage::app()->getRequest()->getParam('process', null);
        if (!$processIds) {
            return false;
        }
        return $eventModel->setInfo(is_array($processIds) ? implode(', ', $processIds) : (int)$processIds);
    }

    /**
     * Custom handler for run Import/Export Profile
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchSystemImportExportRun($config, $eventModel)
    {
        $profile = Mage::registry('current_convert_profile');
        if (!$profile) {
            return false;
        }
        $success = true;
        $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
        if ($messages) {
            $success = 'error' != $messages->getType();
        }
        return $eventModel->setIsSuccess($success)->setInfo($profile->getName() .  ': #' . $profile->getId());
    }

    /**
     * Custom handler for System Currency save
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchSystemCurrencySave($config, $eventModel, $processor)
    {
        $request = Mage::app()->getRequest();
        $change = Mage::getModel('enterprise_logging/event_changes');
        $data = $request->getParam('rate');
        $values = array();
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $currencyCode => $rate) {
            foreach ($rate as $currencyTo => $value) {
                $value = abs($value);
                if ($value == 0) {
                    continue;
                }
                $values[] = $currencyCode . '=>' . $currencyTo . ': ' . $value;
            }
        }

        $processor->addEventChanges($change->setSourceName('rates')
            ->setOriginalData(array())
            ->setResultData(array('rates' => implode(', ', $values))));
        $success = true;
        $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
        if ($messages) {
            $success = 'error' != $messages->getType();
        }
        return $eventModel->setIsSuccess($success)
            ->setInfo(Mage::helper('enterprise_logging')->__('Currency Rates Saved'));
    }

    /**
     * Custom handler for Cache Settings Save
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchSaveCacheSettings($config, $eventModel, $processor)
    {
        $request = Mage::app()->getRequest();
        if (!$request->isPost()) {
            return false;
        }
        $info = '-';
        $cacheTypes = $request->getPost('types');
        if (is_array($cacheTypes) && !empty($cacheTypes)) {
            $cacheTypes = implode(', ', $cacheTypes);
            $info = Mage::helper('enterprise_logging')->__('Cache types: %s ', $cacheTypes);
        }

        $success = true;
        $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
        if ($messages) {
            $success = 'error' != $messages->getType();
        }
        return $eventModel->setIsSuccess($success)->setInfo($info);
    }

    /**
     * Custom tax export handler
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchTaxRatesExport($config, $eventModel)
    {
        if (!Mage::app()->getRequest()->isPost()) {
            return false;
        }
        $success = true;
        $messages = Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage();
        if ($messages) {
            $success = 'error' != $messages->getType();
        }
        return $eventModel->setIsSuccess($success)->setInfo(Mage::helper('enterprise_logging')->__('Tax Rates Export'));
    }

    /**
     * Custom handler for sales archive operations
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchSalesArchiveManagement($config, $eventModel)
    {
        $request = Mage::app()->getRequest();
        $ids = $request->getParam('order_id', $request->getParam('order_ids'));
        if (is_array($ids)) {
            $ids = implode(', ', $ids);
        }
        return $eventModel->setInfo($ids);
    }

    /**
     * Custom handler for Recurring Profiles status update
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event
     */
    public function postDispatchRecurringProfilesUpdate($config, $eventModel)
    {
        $message = '';
        $request = Mage::app()->getRequest();
        if ($request->getParam('action')) {
            $message .= ucfirst($request->getParam('action')) . ' action: ';
        }
        $message .= Mage::getSingleton('adminhtml/session')->getMessages()->getLastAddedMessage()->getCode();
        return $eventModel->setInfo($message);
    }
}
