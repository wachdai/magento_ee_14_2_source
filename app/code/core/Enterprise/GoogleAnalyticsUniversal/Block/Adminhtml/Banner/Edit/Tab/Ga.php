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
 * @package     Enterprise_GoogleAnalyticsUniversal
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GoogleAnalyticsUniversal_Block_Adminhtml_Banner_Edit_Tab_Ga extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Representation value of enabled banner
     *
     */
    const STATUS_ENABLED = 1;

    /**
     * Representation value of disabled banner
     *
     */
    const STATUS_DISABLED  = 0;

    /**
     * Set form id prefix, add customer segment binding, set values if banner is editing
     *
     * @return Enterprise_GoogleAnalyticsUniversal_Block_Adminhtml_Banner_Edit_Tab_Ga
     */
    protected function _prepareForm()
    {
        if (!Mage::helper('enterprise_googleanalyticsuniversal')->isGoogleAnalyticsAvailable()) {
            return $this;
        }

        $form = new Varien_Data_Form();
        $htmlIdPrefix = 'banner_googleanalytics_settings_';
        $form->setHtmlIdPrefix($htmlIdPrefix);

        $model = Mage::registry('current_banner');

        $fieldset = $form->addFieldset('ga_fieldset',
            array('legend' => $this->__('Google Analytics Enhanced Ecommerce Settings'))
        );


        $fieldset->addField('is_ga_enabled', 'select', array(
            'label'     => $this->__('Send to Google'),
            'name'      => 'is_ga_enabled',
            'required'  => false,
            'options'   => array(
                self::STATUS_ENABLED  => $this->__('Yes'),
                self::STATUS_DISABLED => $this->__('No'),
            ),
        ));
        if (!$model->getId()) {
            $model->setData('is_ga_enabled', self::STATUS_ENABLED);
        }

        $fieldset->addField('ga_creative', 'text', array(
            'label'     => $this->__('Creative'),
            'name'      => 'ga_creative',
            'required'  => false,
        ));

        $form->setValues($model->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Google Analytics Enhanced Ecommerce Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Returns status flag whether this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag whether this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
