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
 * Staging schedule configuration tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Edit_Tabs_Schedule extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setFieldNameSuffix('staging');
    }

    /**
     * return html content
     *
     * @return string
     */
    protected function _toHtml()
    {
        $outputFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('general_fieldset',
            array('legend' => Mage::helper('enterprise_staging')->__('Staging Merge Schedule Configuration')));

        $element = $fieldset->addField('schedule_merge_later', 'date', array(
            'label'     => Mage::helper('enterprise_staging')->__('Set Staging Merge Date'),
            'title'     => Mage::helper('enterprise_staging')->__('Set Staging Merge Date'),
            'name'      => 'schedule_merge_later',
            'format'    => $outputFormat,
            'time'      => true,
            'image'     => $this->getSkinUrl('images/grid-cal.gif')
        ));

        return $element->getHtml();
    }
}
