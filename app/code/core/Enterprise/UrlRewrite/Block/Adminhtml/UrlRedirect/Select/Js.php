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
 * @package     Enterprise_UrlRewrite
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * UrlRedirects select js block
 *
 * @category   Enterprise
 * @package    Enterprise_UrlRewrite
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Select_Js extends Mage_Adminhtml_Block_Template
{
    /**
     * Set form id and title
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('urlRedirect_select_js');
    }

    /**
     * Retrieves continue url
     *
     * @return string
     */
    public function getSelectContinueUrl()
    {
        return $this->getUrl('*/*/select', array(
            'type' => '{{type_id}}'
        ));
    }

    /**
     * Retrieves "custom" option type value
     *
     * @return string
     */
    public function getCustomOptionType()
    {
        return 'custom';
    }

    /**
     * Retrieves continue url for edit page
     *
     * @return string
     */
    public function getEditContinueUrl()
    {
        return $this->getUrl('*/*/edit', array(
            $this->getCustomOptionType() => 1,
            'type' => $this->getCustomOptionType()
        ));
    }
}
