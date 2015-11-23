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


/**
 * Enter description here ...
 *
 * @method Enterprise_Banner_Model_Resource_Catalogrule _getResource()
 * @method Enterprise_Banner_Model_Resource_Catalogrule getResource()
 * @method int getBannerId()
 * @method Enterprise_Banner_Model_Catalogrule setBannerId(int $value)
 * @method int getRuleId()
 * @method Enterprise_Banner_Model_Catalogrule setRuleId(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Banner_Model_Catalogrule extends Mage_Core_Model_Abstract
{
    /**
     * Initialize promo catalog price rule model
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_banner/catalogrule');
    }
}
