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
 * @package     Enterprise_Banner
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Banner_Block_Adminhtml_Banner_Edit_Tab_Promotions_Catalogrule extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid, set defaults
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('related_catalogrule_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('related_catalogrule_filter');
        if ($this->_getBanner()->getId()) {
            $this->setDefaultFilter(array('in_banner_catalogrule'=>1));
        }
    }

    /**
     * Set catalor rule collection to grid data
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $bannerId = Mage::registry('current_banner')->getId();
        $collection = Mage::getResourceModel('enterprise_banner/catalogrule_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /* Set custom filter for in banner catalog flag
     *
     * @param string $column
     * @return Enterprise_Banner_Block_Adminhtml_Banner_Edit_Tab_Promotions_Salesrule
     */
    protected function _addColumnFilterToCollection($column)
    {

        if ($column->getId() == 'in_banner_catalogrule') {
            $ruleIds = $this->_getSelectedRules();
            if (empty($ruleIds)) {
                $ruleIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('rule_id', array('in'=>$ruleIds));
            } else {
                if ($ruleIds) {
                    $this->getCollection()->addFieldToFilter('rule_id', array('nin'=>$ruleIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Create grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_banner_catalogrule', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_banner_catalogrule',
            'values'    => $this->_getSelectedRules(),
            'align'     => 'center',
            'index'     => 'rule_id'
        ));
        $this->addColumn('catalogrule_rule_id', array(
            'header'    => Mage::helper('catalogrule')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'rule_id',
        ));

        $this->addColumn('catalogrule_name', array(
            'header'    => Mage::helper('catalogrule')->__('Rule Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));

        $this->addColumn('catalogrule_from_date', array(
            'header'    => Mage::helper('catalogrule')->__('Start Date'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'index'     => 'from_date',
        ));

        $this->addColumn('catalogrule_to_date', array(
            'header'    => Mage::helper('catalogrule')->__('Expiration Date'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'to_date',
        ));

        $this->addColumn('catalogrule_is_active', array(
            'header'    => Mage::helper('catalogrule')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));


        return parent::_prepareColumns();
    }

    /**
     * Ajax grid URL getter
     *
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/catalogRuleGrid', array('_current'=>true));
    }

    protected function _getSelectedRules()
    {
        $rules = $this->getSelectedCatalogRules();
        if (is_null($rules)) {
            $rules = $this->getRelatedCatalogRule();
        }
        return $rules;
    }

    /**
     * Get related sales rules by current banner
     *
     * @return array
     */
    public function getRelatedCatalogRule()
    {
        return $this->_getBanner()->getRelatedCatalogRule();
    }

    /**
     * Get current banner model
     *
     * @return Enterprise_Banner_Model_Banner
     */
    protected function _getBanner()
    {
        return Mage::registry('current_banner');
    }
}
