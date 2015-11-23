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
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_CustomerSegment_Block_Adminhtml_Customersegment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * Intialize form
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('enterprise_customersegment_segment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('enterprise_customersegment')->__('Segment Information'));
    }

    /**
     * Add tab sections
     *
     * @return Enterprise_CustomerSegment_Block_Adminhtml_Customersegment_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $generalSectionContent = $this->getLayout()
            ->createBlock('enterprise_customersegment/adminhtml_customersegment_edit_tab_general')
            ->toHtml();

        $this->addTab('general_section', array(
            'label'   => Mage::helper('enterprise_customersegment')->__('General Properties'),
            'title'   => Mage::helper('enterprise_customersegment')->__('General Properties'),
            'content' => $generalSectionContent,
            'active'  => true
        ));

        $segment = Mage::registry('current_customer_segment');

        if ($segment && $segment->getId()) {
            $conditionsSectionContent = $this->getLayout()
                ->createBlock('enterprise_customersegment/adminhtml_customersegment_edit_tab_conditions')
                ->toHtml();

            $this->addTab('conditions_section', array(
                'label'   => Mage::helper('enterprise_customersegment')->__('Conditions'),
                'title'   => Mage::helper('enterprise_customersegment')->__('Conditions'),
                'content' => $conditionsSectionContent,
            ));

            if ($segment->getApplyTo() != Enterprise_CustomerSegment_Model_Segment::APPLY_TO_VISITORS) {
                $customersQty = Mage::getModel('enterprise_customersegment/segment')->getResource()
                    ->getSegmentCustomersQty($segment->getId());
                $this->addTab('customers_tab', array(
                    'label' => Mage::helper('enterprise_customersegment')->__('Matched Customers (%d)', $customersQty),
                    'url'   => $this->getUrl('*/report_customer_customersegment/customerGrid',
                        array('segment_id' => $segment->getId())),
                    'class' => 'ajax',
                ));
            }
        }

        return parent::_beforeToHtml();
    }

}

