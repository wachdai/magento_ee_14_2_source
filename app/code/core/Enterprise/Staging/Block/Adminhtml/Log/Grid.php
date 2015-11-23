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
 * Staging History Grid
 *
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('enterpriseStagingHistoryGrid');
        $this->setDefaultSort('log_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * PrepareCollection method.
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('enterprise_staging/staging_log_collection');
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * Configuration of grid
     */
    protected function _prepareColumns()
    {
         $this->addColumn('log_id', array(
            'header'    => Mage::helper('enterprise_staging')->__('ID'),
            'index'     => 'log_id',
            'type'      => 'number'
        ));
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('enterprise_staging')->__('Logged At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 200
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('enterprise_staging')->__('Action'),
            'index'     => 'action',
            'type'      => 'options',
            'options'   => Mage::getSingleton('enterprise_staging/staging_config')->getActionLabelsArray(),
            'width' => 200
        ));

        $this->addColumn('from', array(
            'header'    => Mage::helper('enterprise_staging')->__('Website From'),
            'index'     => 'master_website_name',
            'type'      => 'text',
            'renderer' => 'enterprise_staging/adminhtml_log_grid_renderer_website',
            'width'     => 300
        ));

        $this->addColumn('to', array(
            'header'    => Mage::helper('enterprise_staging')->__('Website To'),
            'index'     => 'staging_website_name',
            'type'      => 'text',
            'renderer' => 'enterprise_staging/adminhtml_log_grid_renderer_website',
            'width'     => 300
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('enterprise_staging')->__('Result'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('enterprise_staging/staging_config')->getStatusLabelsArray(),
            'width'  => 100
        ));



        return $this;
    }

    /**
     * Return Row Url
     */
    public function getRowUrl($row)
    {
        if (($row->getStagingWebsiteId() === null && $row->getStagingWebsiteName() !== null) || ($row->getMasterWebsiteId() === null && $row->getMasterWebsiteName() !== null)) {
            return false;
        }
        return $this->getUrl('*/*/view', array(
            'id' => $row->getId())
        );
    }
}
