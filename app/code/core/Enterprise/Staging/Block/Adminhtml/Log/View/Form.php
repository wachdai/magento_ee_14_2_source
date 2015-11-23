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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Staging History Item View
 *
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Log_View_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/staging/log/view.phtml');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Website
     */
    protected function _prepareForm()
    {
        $form       = new Varien_Data_Form();
        $config     = Mage::getSingleton('enterprise_staging/staging_config');
        $log        = $this->getLog();
        $staging    = $log->getStaging();
        $fieldset   = $form->addFieldset('general_fieldset',
            array('legend' => Mage::helper('enterprise_staging')->__('General Information')));

        $fieldset->addField('created_at', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Logged At'),
            'value'     => $this->formatDate($log->getCreatedAt(), 'medium', true)
        ));

        $fieldset->addField('action', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Action'),
            'value'     => Mage::helper('enterprise_staging')->__($config->getActionLabel($log->getAction()))
        ));

        $fieldset->addField('status', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Status'),
            'value'     => Mage::helper('enterprise_staging')->__($config->getStatusLabel($log->getStatus()))
        ));

        $additionalData = $log->getAdditionalData();
        if (!empty($additionalData)) {
            $additionalData = unserialize($additionalData);
            if (is_array($additionalData)) {
                if (isset($additionalData['schedule_date'])) {
                    $fieldset->addField('schedule_date', 'label', array(
                        'label'     => Mage::helper('enterprise_staging')->__('Schedule Date'),
                        'value'     => Mage::helper('core')->formatDate(
                            $additionalData['schedule_date'], Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true)
                    ));
                }
                if(isset($additionalData['action_before_reset'])) {
                   $fieldset->addField('action_before_reset', 'label', array(
                        'label'     => Mage::helper('enterprise_staging')->__('Action Before Resetting'),
                        'value'     => Mage::helper('enterprise_staging')->__($config->getActionLabel($additionalData['action_before_reset']))
                    ));
                }
            }
        }
        if ($log->getAction() == Enterprise_Staging_Model_Staging_Config::ACTION_UNSCHEDULE_MERGE) {
            $mergerUrl = $this->getUrl('*/staging_manage/merge', array('id' => $staging->getId()));
            $fieldset->addField('link_to_staging_merge', 'link', array(
                'href'      => $mergerUrl,
                'label'     => Mage::helper('enterprise_staging')->__('Scheduled Merger'),
                'value'     => $mergerUrl
            ));
        }

        $form->addFieldNameSuffix($this->getFieldNameSuffix());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getInformationHtml(Varien_Object $log)
    {
        return $this->getParentBlock()->getInformationHtml($log);
    }

    /**
     * Retrieve currently viewing log
     *
     * @return Enterprise_Staging_Model_Staging_Log
     */
    public function getLog()
    {
        if (!($this->getData('log') instanceof Enterprise_Staging_Model_Staging_Log)) {
            $this->setData('log', Mage::registry('log'));
        }
        return $this->getData('log');
    }
}
