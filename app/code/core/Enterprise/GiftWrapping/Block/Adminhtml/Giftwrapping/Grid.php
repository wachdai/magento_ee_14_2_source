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
 * @package     Enterprise_GiftWrapping
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Gift Wrapping Grid
 *
 * @category   Enterprise
 * @package    Enterprise_GiftWrapping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Giftwrapping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('giftwrappingGrid');
        $this->setDefaultSort('wrapping_id');
        $this->setDefaultDir('ASC');
    }

    /**
     * Prepare related item collection
     *
     * @return Enterprise_GiftWrapping_Block_Adminhtml_Giftwrapping_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('enterprise_giftwrapping/wrapping')->getCollection()
            ->addStoreAttributesToResult()
            ->addWebsitesToResult();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_GiftWrapping_Block_Adminhtml_Giftwrapping_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('wrapping_id', array(
            'header' => Mage::helper('enterprise_giftwrapping')->__('ID'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'wrapping_id'
        ));

        $this->addColumn('design', array(
            'header' => Mage::helper('enterprise_giftwrapping')->__('Gift Wrapping Design'),
            'index'  => 'design'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('websites', array(
                'header'    => Mage::helper('enterprise_giftwrapping')->__('Websites'),
                'index'     => 'website_ids',
                'type'      => 'options',
                'sortable'  => false,
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash()
            ));
        }

        $statusList = array(
            Mage::helper('enterprise_giftwrapping')->__('Disabled'),
            Mage::helper('enterprise_giftwrapping')->__('Enabled')
        );
        $this->addColumn('status', array(
            'header'  => Mage::helper('enterprise_giftwrapping')->__('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'width'   => '100px',
            'options' => $statusList
        ));

        $this->addColumn('base_price', array(
            'header'  => Mage::helper('enterprise_giftwrapping')->__('Price'),
            'index'   => 'base_price',
            'type'    => 'price',
            'currency_code' => Mage::app()->getWebsite()->getBaseCurrencyCode()
        ));

        $this->addColumn('action',
            array(
                'header'  => Mage::helper('enterprise_giftwrapping')->__('Action'),
                'width'   => '50px',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('enterprise_giftwrapping')->__('Edit'),
                        'url' => array(
                            'base' => '*/*/edit',
                            'params' => array()
                        ),
                        'field' => 'id'
                    )
                ),
                'filter'   => false,
                'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare massaction
     *
     * @return Enterprise_GiftWrapping_Block_Adminhtml_Giftwrapping_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('wrapping_id');
        $this->getMassactionBlock()->setFormFieldName('wrapping_ids');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('enterprise_giftwrapping')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('enterprise_giftwrapping')->__('Are you sure you want to delete the selected gift wrappings?')
        ));

        $statusList = array(
            array('label' => '', 'value' => ''),
            array('label' => Mage::helper('enterprise_giftwrapping')->__('Enabled'), 'value' => '1'),
            array('label' => Mage::helper('enterprise_giftwrapping')->__('Disabled'), 'value' => '0')
        );

        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('enterprise_giftwrapping')->__('Change status'),
            'url'  => $this->getUrl('*/*/changeStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => Mage::helper('enterprise_giftwrapping')->__('Status'),
                    'values' => $statusList
                )
            )
        ));

        return $this;
    }

    /**
     * Retrieve row url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id' => $row->getId()
        ));
    }
}
