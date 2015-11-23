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

/**
 * Customer Segments Grid
 *
 * @category Enterprise
 * @package Enterprise_CustomerSegment
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid Id
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('gridReportCustomersegments');
    }

    /**
     * Add websites and customer count to customer segments collection
     * Set collection
     *
     * @return Enterprise_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Enterprise_CustomerSegment_Model_Mysql4_Segment_Collection */
        $collection = Mage::getModel('enterprise_customersegment/segment')->getCollection();
        $collection->addCustomerCountToSelect()
            ->addWebsitesToResult();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Add grid columns
     *
     * @return Enterprise_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('segment_id', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('ID'),
            'align'     =>'right',
            'width'     => 50,
            'index'     => 'segment_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Segment Name'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Status'),
            'align'     => 'left',
            'width'     => 80,
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website', array(
                'header'    => Mage::helper('enterprise_customersegment')->__('Website'),
                'align'     => 'left',
                'width'     => 200,
                'index'     => 'website_ids',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash()
            ));
        }

        $this->addColumn('customer_count', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Number of Customers'),
            'index'     =>'customer_count',
            'width'     => 200
        ));

        return $this;
    }

    /**
     * Prepare mass action
     *
     * @return Enterprise_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('segment_id');
        $this->getMassactionBlock()->addItem('view', array(
            'label'=> Mage::helper('enterprise_customersegment')->__('View Combined Report'),
            'url'  => $this->getUrl('*/*/detail', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                         'name'     => 'view_mode',
                         'type'     => 'select',
                         'class'    => 'required-entry',
                         'label'    => Mage::helper('enterprise_customersegment')->__('Set'),
                         'values'   => Mage::helper('enterprise_customersegment')->getOptionsArray()
                     )
             )
        ));
        return $this;
    }

    /**
     * Retrieve row click URL
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/detail', array('segment_id' => $row->getId()));
    }
}
