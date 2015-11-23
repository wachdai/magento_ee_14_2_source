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
 * Staging collection
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Staging_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Collection initialization
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_staging/staging');
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        $countSelect->columns('COUNT(main_table.staging_id)');

        return $countSelect;
    }

    /**
     * Set staging website filter into collection
     *
     * @param mixed $stagingWebsiteId (if object must be implemented getId() method)
     * @return Enterprise_Staging_Model_Resource_Staging_Collection
     */
    public function addStagingWebsiteToFilter($stagingWebsiteId)
    {
        if (is_object($stagingWebsiteId)) {
            $stagingWebsiteId = $stagingWebsiteId->getId();
        }
        $this->addFieldToFilter('staging_website_id', (int) $stagingWebsiteId);

        return $this;
    }

    /**
     * Joining website name
     *
     * @return Enterprise_Staging_Model_Resource_Staging_Collection
     */
    public function addWebsiteName()
    {
        $this->getSelect()->joinLeft(
            array('site'=>$this->getTable('core/website')),
            "main_table.staging_website_id = site.website_id",
            array('name' => 'site.name')
        );

       return $this;
    }

    /**
     * Joining last log id and log action
     *
     * @return Enterprise_Staging_Model_Resource_Staging_Collection
     */
    public function addLastLogComment()
    {
        $helper     = Mage::getResourceHelper('enterprise_staging');

        $subSelect = clone $this->getSelect();
        $subSelect->reset();
        $subSelect = $helper->getLastStagingLogQuery($this->getTable('enterprise_staging/staging_log'), $subSelect);

        $this->getSelect()
            ->joinLeft(
                array('staging_log' => new Zend_Db_Expr('(' . $subSelect . ')')),
                'main_table.staging_id = staging_log.staging_id',
                array('log_id', 'action'));
        return $this;
    }

    /**
     * Convert items array to array for select options
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @return array
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('staging_id', 'name');
    }

    /**
     * Convert items array to hash for select options
     * array($value => $label)
     *
     * @return array
     */
    public function toOptionHash()
    {
        return parent::_toOptionHash('staging_id', 'name');
    }

    /**
     * Set staging is scheduled flag filter into collection
     *
     * @return Enterprise_Staging_Model_Resource_Staging_Collection
     */
    public function addIsSheduledToFilter()
    {
        $this->addFieldToFilter('merge_scheduling_date', array('notnull' => true));
        return $this;
    }
}
