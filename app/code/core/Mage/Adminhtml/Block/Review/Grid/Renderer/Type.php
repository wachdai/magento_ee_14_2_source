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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Adminhtml review grid item renderer for item type
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Review_Grid_Renderer_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {

        if (is_null($row->getCustomerId())) {
            if ($row->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID) {
                return Mage::helper('review')->__('Administrator');
            } else {
                return Mage::helper('review')->__('Guest');
            }
        } elseif ($row->getCustomerId() > 0) {
            return Mage::helper('review')->__('Customer');
        }
//		return ($row->getCustomerId() ? Mage::helper('review')->__('Customer') : Mage::helper('review')->__('Guest'));
    }
}// Class Mage_Adminhtml_Block_Review_Grid_Renderer_Type END
