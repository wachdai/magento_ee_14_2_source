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
 * Adminhtml page breadcrumbs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Widget_Breadcrumbs extends Mage_Adminhtml_Block_Template
{
    /**
     * breadcrumbs links
     *
     * @var array
     */
    protected $_links = array();

    public function __construct()
    {
        $this->setTemplate('widget/breadcrumbs.phtml');
        $this->addLink(Mage::helper('adminhtml')->__('Home'), Mage::helper('adminhtml')->__('Home'), $this->getUrl('*'));
    }

    public function addLink($label, $title=null, $url=null)
    {
        if (empty($title)) {
            $title = $label;
        }
        $this->_links[] = array(
            'label' => $label,
            'title' => $title,
            'url'   => $url
        );
        return $this;
    }

    protected function _beforeToHtml()
    {
        // TODO - Moved to Beta 2, no breadcrumbs displaying in Beta 1
        // $this->assign('links', $this->_links);
        return parent::_beforeToHtml();
    }
}
