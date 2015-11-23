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
 * @package     Enterprise_Mview
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise_Mview_Model_Metadata
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method Enterprise_Mview_Model_Metadata setId(int $id)
 * @method Enterprise_Mview_Model_Metadata setKeyColumn(string $key)
 * @method Enterprise_Mview_Model_Metadata setViewName(string $table)
 * @method Enterprise_Mview_Model_Metadata setVersionId(string $table)
 * @method Enterprise_Mview_Model_Metadata setStatus(int $status)
 * @method Enterprise_Mview_Model_Metadata setChangelogName(string $name)
 * @method int getStatus()
 * @method string getChangelogName()
 * @method string getVersionId()
 * @method string getKeyColumn()
 * @method void save()
 * @method string getTableName()
 */
class Enterprise_Mview_Model_Metadata extends Mage_Core_Model_Abstract
{
    /**
     * Valid status is used for actual data
     */
    const STATUS_VALID          = 1;

    /**
     * Invalid status.
     * Used when data in table is broken or does not exist
     */
    const STATUS_INVALID        = 2;

    /**
     * In progress status
     */
    const STATUS_IN_PROGRESS    = 3;

    /**
     * Metadata group code
     *
     * @var string
     */
    protected $_groupCode;

    /**
     * Model initialization
     */
    protected function _construct()
    {
        $this->_init('enterprise_mview/metadata');
    }

    /**
     * Set valid status
     *
     * @return Enterprise_Mview_Model_Metadata
     */
    public function setValidStatus()
    {
        return $this->setStatus(self::STATUS_VALID);
    }

    /**
     * Set invalid status
     *
     * @return Enterprise_Mview_Model_Metadata
     */
    public function setInvalidStatus()
    {
        return $this->setStatus(self::STATUS_INVALID);
    }

    /**
     * Set in progress status
     *
     * @return Enterprise_Mview_Model_Metadata
     */
    public function setInProgressStatus()
    {
        return $this->setStatus(self::STATUS_IN_PROGRESS);
    }

    /**
     * Check metadata
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->getId() && $this->getChangelogName();
    }

    /**
     * Set metadata group code
     *
     * @param string $code
     * @return Enterprise_Mview_Model_Metadata
     */
    public function setGroupCode($code)
    {
        $this->_groupCode = $code;
        return $this;
    }

    /**
     * Processing metadata group before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if (!$this->_groupCode) {
            return parent::_beforeSave();
        }
        /** @var $groupModel Enterprise_Mview_Model_Metadata_Group */
        $groupModel = Mage::getModel('enterprise_mview/metadata_group')->loadByCode($this->_groupCode);

        if (!$groupModel->getId()) {
            $groupModel->setGroupCode($this->_groupCode)->save();
        }

        $this->setGroupId($groupModel->getId());

        return parent::_beforeSave();
    }
}
