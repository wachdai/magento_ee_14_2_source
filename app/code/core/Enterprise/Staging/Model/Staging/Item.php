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
 * Staging item model
 *
 * @method Enterprise_Staging_Model_Resource_Staging_Item _getResource()
 * @method Enterprise_Staging_Model_Resource_Staging_Item getResource()
 * @method int getStagingId()
 * @method Enterprise_Staging_Model_Staging_Item setStagingId(int $value)
 * @method string getCode()
 * @method Enterprise_Staging_Model_Staging_Item setCode(string $value)
 * @method int getSortOrder()
 * @method Enterprise_Staging_Model_Staging_Item setSortOrder(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Staging_Item extends Mage_Core_Model_Abstract
{
    /**
     * constructor
     */
    protected function _construct()
    {
        $this->_init('enterprise_staging/staging_item');
    }

    public function loadFromXmlStagingItem($xmlItem)
    {
        $this->setData('code', (string) $xmlItem->getName());
        $name = Mage::helper('enterprise_staging')->__((string) $xmlItem->label);
        $this->setData('name', $name);
        return $this;
    }
}
