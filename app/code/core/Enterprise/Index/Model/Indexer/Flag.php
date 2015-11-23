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
 * @package     Enterprise_Index
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise Index Indexer Flag
 *
 * @category   Enterprise
 * @package    Enterprise_Index
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Indexer_Flag extends Mage_Core_Model_Flag
{
    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = 'enterprise_changelog_indexer_flag';

    /**
     * Retrieve Enterprise Index Indexer Fla Data is in progress flag
     *
     * @return bool
     */
    public function getIsProcessing()
    {
        $flagData = $this->load($this->_flagCode, 'flag_code')
            ->getFlagData();

        if (!isset($flagData['is_processing'])) {
            $flagData['is_processing'] = false;
            $this->setFlagData($flagData)->save();
        }

        return (bool)$flagData['is_processing'];
    }

    /**
     * Set Enterprise Index Indexer Fla Data is in progress flag
     *
     * @param bool $flag
     *
     * @return Mage_Catalog_Model_Product_Flat_Flag
     */
    public function setIsProcessing($flag = true)
    {
        $flagData = array();
        $flagData['is_processing'] = (bool)$flag;
        $this->setFlagData($flagData);
        $this->save();
        return $this;
    }
}
