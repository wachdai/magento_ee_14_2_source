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
 * Staging entities tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Edit_Tabs_Item extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Keep main translate helper instance
     *
     * @var object
     */
    protected $helper;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFieldNameSuffix('staging[items]');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Item
     */
    protected function _prepareForm()
    {
        $form          = new Varien_Data_Form();

        $staging       = $this->getStaging();
        $collection    = $staging->getItemsCollection();

        $fieldset = $form->addFieldset('staging_dataset_item',
            array('legend' => Mage::helper('enterprise_staging')->__('Select Items to be Merged')));

        $extendInfo = $this->getExtendInfo();

        foreach (Mage::getSingleton('enterprise_staging/staging_config')->getStagingItems() as $stagingItem) {
            if ((int)$stagingItem->is_backend) {
                continue;
            }

            $_code      = (string) $stagingItem->getName();
            $disabled   = "none";
            $note       = "";

            //process extend information
            if (!empty($extendInfo) && is_array($extendInfo)) {
                if ($extendInfo[$_code]["disabled"]==true) {
                    $disabled = "disabled";
                    $note = '<div style="color:#900">'.$extendInfo[$_code]["note"] . "<div>";
                } else {
                    $note = '<div style="color:#090">'.$extendInfo[$_code]["note"] . "<div>";
                }
            }

            $fieldset->addField('staging_item_code_'.$_code, 'checkbox',
                array(
                    'label'    => Mage::helper('enterprise_staging')->__((string)$stagingItem->label),
                    'name'     => "{$_code}[staging_item_code]",
                    'value'    => $_code,
                    'checked'  => true,
                    $disabled  => true,
                    'note'     => $note,
                )
            );
        }

        $form->setFieldNameSuffix($this->getFieldNameSuffix());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrive current staging object
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function getStaging()
    {
        if (!($this->getData('staging') instanceof Enterprise_Staging_Model_Staging)) {
            $this->setData('staging', Mage::registry('staging'));
        }
        return $this->getData('staging');
    }
}
