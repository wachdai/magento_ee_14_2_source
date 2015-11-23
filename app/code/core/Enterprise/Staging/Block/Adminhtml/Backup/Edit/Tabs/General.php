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
 * Staging backup general info tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Backup_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form
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
        $this->setFieldNameSuffix('staging_backup');
    }

    /**
     * Prepare form fieldset and form values
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('staging_backup_general_fieldset',
            array('legend' => Mage::helper('enterprise_staging')->__('Backup Main Information')));

        $fieldset->addField('name', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Backup Name'),
            'title'     => Mage::helper('enterprise_staging')->__('Backup Name'),
            'value'     => $this->getBackupName()
        ));

        $fieldset->addField('staging_name', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Staging Website'),
            'title'     => Mage::helper('enterprise_staging')->__('Staging Website'),
            'value'     => $this->getStagingWebsiteName()
        ));

        $fieldset->addField('master_website', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Master Website'),
            'title'     => Mage::helper('enterprise_staging')->__('Master Website'),
            'value'     => $this->getMasterWebsiteName()
        ));

        $fieldset->addField('backupCreateAt', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Created Date'),
            'title'     => Mage::helper('enterprise_staging')->__('Created Date'),
            'value'     => $this->formatDate($this->getBackup()->getCreatedAt(), 'medium', true)
        ));

        $fieldset->addField('tablePrefix', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Table Prefix'),
            'title'     => Mage::helper('enterprise_staging')->__('Table Prefix'),
            'value'     => $this->getBackup()->getStagingTablePrefix()
        ));

        $form->setFieldNameSuffix($this->getFieldNameSuffix());

        $this->setForm($form);

        return parent::_prepareForm();
    }


    /**
     * Retrieve master website name (if website exists)
     *
     * @return string
     */
    public function getMasterWebsiteName()
    {
        return $this->_getWebsiteName($this->getBackup()->getMasterWebsiteId());
    }

    /**
     * Retrieve staging website name (if website exists)
     *
     * @return string
     */
    public function getStagingWebsiteName()
    {
        return $this->_getWebsiteName($this->getBackup()->getStagingWebsiteId());
    }

    /**
     * Custom getter of website name by specified website Id
     *
     * @param int $websiteId
     * @return string
     */
    protected function _getWebsiteName($websiteId)
    {
        if ($websiteId) {
            $website = Mage::app()->getWebsite($websiteId);
            if ($website) {
                return $website->getName();
            }
        }
        return Mage::helper('enterprise_staging')->__('No information');
    }

    /**
     * Retrieve currently edited backup object
     *
     * @return Enterprise_Staging_Model_Staging_Backup
     */
    public function getBackup()
    {
        if (!($this->getData('staging_backup') instanceof Enterprise_Staging_Model_Staging_Backup)) {
            $this->setData('staging_backup', Mage::registry('staging_backup'));
        }
        return $this->getData('staging_backup');
    }

    /**
     * Backup name getter
     *
     * @return string
     */
    public function getBackupName()
    {
        return $this->getBackup()->getName();
    }
}
