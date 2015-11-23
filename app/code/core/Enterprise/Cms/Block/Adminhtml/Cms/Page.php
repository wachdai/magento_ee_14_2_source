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
 * Adminhtml cms pages content block
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Cms_Block_Adminhtml_Cms_Page extends Mage_Adminhtml_Block_Template
{
    /**
     * Add  column Versioned to cms page grid
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Page
     */
    protected function _prepareLayout()
    {
        /* @var $pageGrid Mage_Adminhtml_Block_Cms_Page_Grid */
        $page = $this->getLayout()->getBlock('cms_page');
        if ($page) {
            $pageGrid = $page->getChild('grid');
            if($pageGrid) {
                $pageGrid->addColumnAfter('versioned', array(
                    'index'     => 'under_version_control',
                    'header'    => Mage::helper('enterprise_cms')->__('Version Control'),
                    'width'     => 10,
                    'type'      => 'options',
                    'options'   => array(Mage::helper('enterprise_cms')->__('No'),
                        Mage::helper('enterprise_cms')->__('Yes')
                    )
                ), 'page_actions');
            }
        }

        return $this;
    }
}
