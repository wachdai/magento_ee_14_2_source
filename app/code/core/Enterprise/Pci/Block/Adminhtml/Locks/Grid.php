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
 * @package     Enterprise_Pci
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Locked administrators grid
 *
 */
class Enterprise_Pci_Block_Adminhtml_Locks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set misc grid data
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setId('lockedAdminsGrid')->setDefaultSort('user_id')->setUseAjax(true);
    }

    /**
     * Instantiate collection
     *
     * @return Mage_Admin_Model_Mysql4_User_Collection
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getResourceModel('admin/user_collection');
            $this->_collection->addFieldToFilter('lock_expires', array('notnull' => true));
        }
        return $this->_collection;
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_Pci_Block_Adminhtml_Locks_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('user_id', array(
            'header' => Mage::helper('enterprise_pci')->__('ID'),
            'index'  => 'user_id',
            'width'  => 50,
            'filter' => false,
            'type'   => 'number'
        ));
        $this->addColumn('username', array(
            'header' => Mage::helper('enterprise_pci')->__('Username'),
            'index'  => 'username',
        ));
        $this->addColumn('last_login', array(
            'header' => Mage::helper('enterprise_pci')->__('Last login'),
            'index'  => 'logdate',
            'filter' => false,
            'type'   => 'datetime',
        ));
        $this->addColumn('failures_num', array(
            'header' => Mage::helper('enterprise_pci')->__('Failures'),
            'index'  => 'failures_num',
            'filter' => false,
        ));
        $this->addColumn('lock_expires', array(
            'header'  => Mage::helper('enterprise_pci')->__('Locked until'),
            'index'   => 'lock_expires',
            'filter'  => false,
            'type'    => 'datetime',
        ));

        $this->setDefaultFilter(array('lock_expires' => 1));

        return parent::_prepareColumns();
    }

    /**
     * Add massaction to grid
     *
     * @return Enterprise_Pci_Block_Adminhtml_Locks_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('user_id');
        $this->getMassactionBlock()->setFormFieldName('unlock');

        $this->getMassactionBlock()->addItem('unlock', array(
             'label'    => Mage::helper('enterprise_pci')->__('Unlock'),
             'url'      => $this->getUrl('*/*/massUnlock'),
             'selected' => true,
        ));

        return $this;
    }

    /**
     * Get grid URL
     *
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }
}
