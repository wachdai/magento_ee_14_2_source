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

class Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set default sort
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('giftregistryGrid');
        $this->setDefaultSort('type_id');
        $this->setDefaultDir('ASC');
    }

    /**
     * Instantiate and prepare collection
     *
     * @return Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('enterprise_giftregistry/type')->getCollection()
            ->addStoreData();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for grid
     *
     * @return Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('type_id', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('ID'),
            'align'  => 'right',
            'width'  => 50,
            'index'  => 'type_id'
        ));

        $this->addColumn('code', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Code'),
            'index'  => 'code'
        ));


        $this->addColumn('label', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Label'),
            'index'  => 'label'
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('enterprise_giftregistry')->__('Sort Order'),
            'index'  => 'sort_order',
            'default' => '-'
        ));

        $this->addColumn('is_listed', array(
            'header'  => Mage::helper('enterprise_giftregistry')->__('Is Listed'),
            'index'   => 'is_listed',
            'type'    => 'options',
            'options' => array(
                '0' => Mage::helper('enterprise_giftregistry')->__('No'),
                '1' => Mage::helper('enterprise_giftregistry')->__('Yes')
            )
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
            'id'    => $row->getId()
        ));
    }
}
