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
 * Staging Manage Grid
 *
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('enterpriseStagingManageGrid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->setColumnRenderers(
            array(
                'action' => 'enterprise_staging/adminhtml_widget_grid_column_renderer_action'
        ));
    }

    /**
     * PrepareCollection method.
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('enterprise_staging/staging_collection')
            ->addWebsiteName()
            ->addLastLogComment();

        $this->setCollection($collection);

        parent::_prepareCollection();

        foreach($collection AS $staging) {
            $defaultStore = $staging->getStagingWebsite()->getDefaultStore();
            if ($defaultStore) {
                if ($defaultStore->isFrontUrlSecure()) {
                    $baseUrl = $defaultStore->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true);
                } else {
                    $baseUrl = $defaultStore->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                }
            } else {
                $baseUrl = '';
            }

            $collection->getItemById($staging->getId())
                ->setData("base_url", $baseUrl);
        }

        return $this;
    }

    /**
     * Configuration of grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('enterprise_staging')->__('Website Name'),
            'index'     => 'name',
            'type'      => 'text',
        ));

        $this->addColumn('base_url', array(
            'width'     => 250,
            'header'    => Mage::helper('enterprise_staging')->__('URL'),
            'index'     => 'base_url',
            'title'     => 'base_url',
            'length'    => '40',
            'type'      => 'action',
            'link_type' => 'url',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('status', array(
            'width'     => 100,
            'header'    => Mage::helper('enterprise_staging')->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array('started' => Mage::helper('enterprise_staging')->__('Processing'), 'completed' => Mage::helper('enterprise_staging')->__('Ready'))
        ));

        $this->addColumn('last_event', array(
            'width'     => 250,
            'header'    => Mage::helper('enterprise_staging')->__('Latest Event'),
            'index'     => 'action',
            'sortable' => false,
            'filter'   => false,
            'renderer' => 'enterprise_staging/adminhtml_staging_grid_renderer_event',
            'options'   => Mage::getSingleton('enterprise_staging/staging_config')->getActionLabelsArray()
        ));

        $this->addColumn('created_at', array(
            'width'     => 100,
            'header'    => Mage::helper('enterprise_staging')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('updated_at', array(
            'width'     => 100,
            'header'    => Mage::helper('enterprise_staging')->__('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('enterprise_staging')->__('Action'),
            'type'      => 'action',
            'getter'    => 'getId',
            'width'     => 80,
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'type',
            'link_type' => 'actions',
            'actions'   => array(
                //array(
                //    'url'       => $this->getUrl('*/*/edit', array('id' => '$staging_id')),
                //    'caption'   => Mage::helper('enterprise_staging')->__('Edit')
                //),
                array(
                    'url'       => $this->getUrl('*/*/merge', array('id' => '$staging_id')),
                    'caption'   => Mage::helper('enterprise_staging')->__('Merge'),
                    'validate'  => array(
                        '__method_callback' => array(
                            'method' => 'canMerge'
                    ))
                ),
                array(
                    'url'       => $this->getUrl('*/*/unschedule', array('id' => '$staging_id')),
                    'caption'   => Mage::helper('enterprise_staging')->__('Unschedule'),
                    'validate'  => array(
                        '__method_callback' => array(
                            'method' => 'canUnschedule'
                    ))
                ),
                array(
                    'url'       => $this->getUrl('*/*/resetStatus', array('id' => '$staging_id')),
                    'caption'   => Mage::helper('enterprise_staging')->__('Reset Status'),
                    'validate'  => array(
                        '__method_callback' => array(
                            'method' => 'canResetStatus'
                    ))
                )
            )
        ));

        return $this;
    }

    /**
     * Return grids url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * Return Row Url
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id' => $row->getId())
        );
    }
}
