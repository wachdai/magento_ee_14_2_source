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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GiftRegistry_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set default sort
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('customerGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('registry_id');
        $this->setDefaultDir('ASC');
    }

    /**
     * Instantiate and prepare collection
     *
     * @return Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Customer_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Enterprise_GiftRegistry_Model_Resource_Entity_Collection */
        $collection = Mage::getModel('enterprise_giftregistry/entity')->getCollection();
        $collection->filterByCustomerId($this->getRequest()->getParam('id'));
        $collection->addRegistryInfo();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for grid
     *
     * @return Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Customer_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Event'),
            'index'  => 'title'
        ));

        $this->addColumn('registrants', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Recipients'),
            'index'  => 'registrants'
        ));

        $this->addColumn('event_date', array(
            'header'  => Mage::helper('enterprise_giftregistry')->__('Event Date'),
            'index'   => 'event_date',
            'type'    => 'date',
            'default' => '--'
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Total Items'),
            'index'  => 'qty',
            'type'   => 'number'
        ));

        $this->addColumn('qty_fulfilled', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Items Fulfilled'),
            'index'  => 'qty_fulfilled',
            'type'   => 'number',
        ));

        $this->addColumn('qty_remaining', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Items Remaining'),
            'index'  => 'qty_remaining',
            'type'   => 'number'
        ));

        $this->addColumn('is_public', array(
            'header'  => Mage::helper('enterprise_giftregistry')->__('Is Public'),
            'index'   => 'is_public',
            'type'    => 'options',
            'options' => array(
                '0' => Mage::helper('enterprise_giftregistry')->__('No'),
                '1' => Mage::helper('enterprise_giftregistry')->__('Yes'),
            )
        ));

        $this->addColumn('website_id', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Website'),
            'index'  => 'website_id',
            'type'   => 'options',
            'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash()
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve row url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id'       => $row->getId(),
            'customer' => $row->getCustomerId()
        ));
    }

    /**
     * Retrieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
