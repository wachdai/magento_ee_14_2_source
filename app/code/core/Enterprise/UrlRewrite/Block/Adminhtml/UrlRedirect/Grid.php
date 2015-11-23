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
 * @package     Enterprise_UrlRewrite
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Adminhtml urlredirect grid block
 *
 * @category   Enterprise
 * @package    Enterprise_UrlRewrite
 * @author     Magento Core Team <core@magentocommerce.com>
 * @method Enterprise_UrlRewrite_Model_Resource_Redirect_Collection getCollection()
 */
class Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Prepare default data
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('urlRedirectGrid');
        $this->setDefaultSort('redirect_id');
    }

    /**
     * Init collection
     *
     * @return Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('enterprise_urlrewrite/redirect')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('redirect_id', array(
            'header'    => $this->__('ID'),
            'width'     => '50px',
            'index'     => 'redirect_id'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => $this->__('Store View'),
                'type' => 'store',
                'skipAllStoresLabel' => true,
                'index' => 'store_id',
                'sortable' => false,
                'store_view' => true,
                'width' => '50px'
            ));
        }

        $this->addColumn('identifier', array(
            'header'    => $this->__('Request Path'),
            'width'     => '50px',
            'index'     => 'identifier'
        ))->addColumn('target_path', array(
            'header'    => $this->__('Target Path'),
            'width'     => '50px',
            'index'     => 'target_path'
        ))->addColumn('options', array(
            'header'    => $this->__('Options'),
            'width'     => '50px',
            'index'     => 'options'
        ))->addColumn('description', array(
            'header'    => $this->__('Description'),
            'width'     => '50px',
            'index'     => 'description'
        ))->addColumn('actions', array(
            'header'    => $this->__('Action'),
            'width'     => '15px',
            'sortable'  => false,
            'filter'    => false,
            'type'      => 'action',
            'actions'   => array(
                array(
                    'url'       => $this->getUrl('*/*/edit', array('id' => '$redirect_id', 'type' => 'custom')),
                    'caption'   => $this->__('Edit'),
                ),
            )
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get row url
     *
     * @param Enterprise_UrlRewrite_Model_Redirect $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'type' => 'custom'));
    }
}

