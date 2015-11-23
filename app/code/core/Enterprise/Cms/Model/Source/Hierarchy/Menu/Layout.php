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
 * CMS Hierarchy Menu source model for Layouts
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Model_Source_Hierarchy_Menu_Layout
{
    /**
     * Return options for displaying Hierarchy Menu
     *
     * @param bool $withDefault Include or not default value
     * @return array
     */
    public function toOptionArray($withDefault = false)
    {
        $options = array();
        if ($withDefault) {
           $options[] = array('label' => Mage::helper('enterprise_cms')->__('Use default'), 'value' => '');
        }

        foreach (Mage::getSingleton('enterprise_cms/hierarchy_config')->getContextMenuLayouts() as $code => $info) {
            $options[] = array(
                'label' => $info->getLabel(),
                'value' => $code
            );
        }

        return $options;
    }
}
