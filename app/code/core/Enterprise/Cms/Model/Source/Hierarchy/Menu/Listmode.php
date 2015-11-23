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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * CMS Hierarchy Navigation Menu source model for Display list mode
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Model_Source_Hierarchy_Menu_Listmode
{
    /**
     * Retrieve options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            ''          => Mage::helper('enterprise_cms')->__('Default'),
            '1'         => Mage::helper('enterprise_cms')->__('Numbers (1, 2, 3, ...)'),
            'a'         => Mage::helper('enterprise_cms')->__('Lower Alpha (a, b, c, ...)'),
            'A'         => Mage::helper('enterprise_cms')->__('Upper Alpha (A, B, C, ...)'),
            'i'         => Mage::helper('enterprise_cms')->__('Lower Roman (i, ii, iii, ...)'),
            'I'         => Mage::helper('enterprise_cms')->__('Upper Roman (I, II, III, ...)'),
            'circle'    => Mage::helper('enterprise_cms')->__('Circle'),
            'disc'      => Mage::helper('enterprise_cms')->__('Disc'),
            'square'    => Mage::helper('enterprise_cms')->__('Square'),
        );
    }
}
