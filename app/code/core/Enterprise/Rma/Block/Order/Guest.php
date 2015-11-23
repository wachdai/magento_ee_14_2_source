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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class Enterprise_Rma_Block_Order_Guest extends Mage_Core_Block_Template
{
    public function _construct()
    {
        parent::_construct();

        if (Mage::helper('enterprise_rma')->isEnabled()) {
            $returns = Mage::getResourceModel('enterprise_rma/rma_grid_collection')
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', Mage::registry('current_order')->getId())
                ->count()
            ;

            if (!empty($returns)) {
                Mage::app()->getLayout()
                    ->getBlock('sales.order.info')
                    ->addLink('returns', 'rma/guest/returns', 'Returns');
            }
        }
    }
}
