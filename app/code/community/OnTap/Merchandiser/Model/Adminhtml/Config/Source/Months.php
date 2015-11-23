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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class OnTap_Merchandiser_Model_Adminhtml_Config_Source_Months
{
    /**
     * toOptionArray function.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => "1"),
            array('value' => 2, 'label' => "2"),
            array('value' => 3, 'label' => "3"),
            array('value' => 4, 'label' => "4"),
            array('value' => 5, 'label' => "5"),
            array('value' => 6, 'label' => "6"),
            array('value' => 7, 'label' => "7"),
            array('value' => 8, 'label' => "8"),
            array('value' => 9, 'label' => "9"),
            array('value' => 10, 'label' => "10"),
            array('value' => 11, 'label' => "11"),
            array('value' => 12, 'label' => "12"),
        );
    }
}
