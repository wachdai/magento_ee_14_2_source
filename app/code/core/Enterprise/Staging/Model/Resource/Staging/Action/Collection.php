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
 * Staging action collection
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Staging_Action_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Collection initialization
     *
     */
    public function _construct()
    {
        $this->_init('enterprise_staging/staging_action');
    }

    /**
     * Set staging filter into collection
     *
     * @param mixed $stagingId (if object must be implemented getId() method)
     * @return Enterprise_Staging_Model_Resource_Staging_Action_Collection
     */
    public function setStagingFilter($stagingId)
    {
        if ($stagingId instanceof Varien_Object) {
            $stagingId = $stagingId->getId();
        }
        $this->addFieldToFilter('staging_id', (int) $stagingId);

        return $this;
    }

    /**
     * Set event filter into collection
     *
     * @param mixed $eventId (if object must be implemented getId() method)
     * @return Enterprise_Staging_Model_Resource_Staging_Action_Collection
     */
    public function setEventFilter($eventId)
    {
        if (is_object($eventId)) {
            $eventId = $eventId->getId();
        }
        $this->addFieldToFilter('event_id', (int) $eventId);

        return $this;
    }

    /**
     * Add backuped filter
     *
     * @deprecated after 1.8.0.0
     *
     * @return Enterprise_Staging_Model_Resource_Staging_Action_Collection
     */
    public function addBackupedFilter()
    {
        //        $this->addFieldToFilter('main_table.is_backuped', 1);

        return $this;
    }

    /**
     * Add staging to collection
     *
     * @return Enterprise_Staging_Model_Resource_Staging_Action_Collection
     */
    public function addStagingToCollection()
    {
        $this->getSelect()
            ->joinLeft(
                array('staging' => $this->getTable('enterprise_staging/staging')),
                'main_table.staging_id=staging.staging_id',
                array('staging_name'=>'name')
        );

        return $this;
    }

    /**
     * Add websites to collection
     *
     * @return Enterprise_Staging_Model_Resource_Staging_Action_Collection
     */
    public function addWebsitesToCollection()
    {
        $this->getSelect()
            ->joinLeft(
                array('core_website' => $this->getTable('core/website')),
                'main_table.master_website_id=core_website.website_id',
                array('master_website_id' => 'website_id',
                    'master_website_name' => 'name'))
            ->joinLeft(
                array('staging_website' => $this->getTable('core/website')),
                'main_table.staging_website_id=staging_website.website_id',
                array('staging_website_id' => 'website_id',
                    'staging_website_name' => 'name')
        );
        
        Mage::getResourceHelper('core')->prepareColumnsList($this->getSelect());

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
        return parent::_toOptionArray('backup_id', 'name');
    }

    /**
     * Convert items array to hash for select options
     * array($value => $label)
     *
     * @return array
     */
    public function toOptionHash()
    {
        return parent::_toOptionHash('backup_id', 'name');
    }
}
