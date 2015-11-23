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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Grid with revisions on version page
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Cms_Block_Adminhtml_Cms_Page_Version_Edit_Revisions
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('revisionsGrid');
        $this->setDefaultSort('revision_number');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * Prepares collection of revisions
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Page_Version_Edit_Revisions
     */
    protected function _prepareCollection()
    {
        /* var $collection Enterprise_Cms_Model_Mysql4_Revision_Collection */
        $collection = Mage::getModel('enterprise_cms/page_revision')->getCollection()
            ->addPageFilter($this->getPage())
            ->addVersionFilter($this->getVersion())
            ->addUserColumn()
            ->addUserNameColumn();

            // Commented this bc now revision are shown in scope of version and not page.
            // So if user has permission to load this version he
            // has permission to see all its versions
            //->addVisibilityFilter(Mage::getSingleton('admin/session')->getUser()->getId(),
            //    Mage::getSingleton('enterprise_cms/config')->getAllowedAccessLevel());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Retrieve collection for grid if there is not collection call _prepareCollection
     *
     * @return Enterprise_Cms_Model_Mysql4_Page_Version_Collection
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_prepareCollection();
        }

        return $this->_collection;
    }

    /**
     * Prepare event grid columns
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Page_Version_Edit_Revisions
     */
    protected function _prepareColumns()
    {
/*
        $this->addColumn('version_number', array(
            'header' => Mage::helper('enterprise_cms')->__('Version #'),
            'width' => 100,
            'index' => 'version_number',
            'type' => 'options',
            'options' => Mage::helper('enterprise_cms')->getVersionsArray($this->getPage())
        ));

        $this->addColumn('label', array(
            'header' => Mage::helper('enterprise_cms')->__('Version Label'),
            'index' => 'label',
            'type' => 'options',
            'options' => Mage::helper('enterprise_cms')
                                ->getVersionsArray('label', 'label', $this->getPage())
        ));

        $this->addColumn('access_level', array(
            'header' => Mage::helper('enterprise_cms')->__('Access Level'),
            'index' => 'access_level',
            'type' => 'options',
            'width' => 100,
            'options' => Mage::helper('enterprise_cms')->getVersionAccessLevels()
        ));
*/
        $this->addColumn('revision_number', array(
            'header' => Mage::helper('enterprise_cms')->__('Revision #'),
            'width' => 200,
            'type' => 'number',
            'index' => 'revision_number'
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('enterprise_cms')->__('Created'),
            'index' => 'created_at',
            'type' => 'datetime',
            'filter_time' => true,
            'width' => 250
        ));

        $this->addColumn('author', array(
            'header' => Mage::helper('enterprise_cms')->__('Author'),
            'index' => 'user',
            'type' => 'options',
            'options' => $this->getCollection()->getUsersArray()
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare url for reload grid through ajax
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/revisions', array('_current'=>true));
    }

    /**
     * Grid row event edit url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/cms_page_revision/edit', array('page_id' => $row->getPageId(), 'revision_id' => $row->getRevisionId()));
    }

    /**
     * Returns cms page object from registry
     *
     * @return Mage_Cms_Model_Page
     */
    public function getPage()
    {
        return Mage::registry('cms_page');
    }

    /**
     * Returns cms page version object from registry
     *
     * @return Enterprise_Cms_Model_Page_Version
     */
    public function getVersion()
    {
        return Mage::registry('cms_page_version');
    }

    /**
     * Prepare massactions for this grid.
     * For now it is only ability to remove revisions
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Page_Version_Edit_Revisions
     */
    protected function _prepareMassaction()
    {
        if (Mage::getSingleton('enterprise_cms/config')->canCurrentUserDeleteRevision()) {
            $this->setMassactionIdField('revision_id');
            $this->getMassactionBlock()->setFormFieldName('revision');

            $this->getMassactionBlock()->addItem('delete', array(
                 'label'=> Mage::helper('enterprise_cms')->__('Delete'),
                 'url'  => $this->getUrl('*/*/massDeleteRevisions', array('_current' => true)),
                 'confirm' => Mage::helper('enterprise_cms')->__('Are you sure?')
            ));
        }
        return $this;
    }
}
