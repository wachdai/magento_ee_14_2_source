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
 * Container block
 *
 * @category   Enterprise
 * @package    Enterprise_UrlRewrite
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Container extends Mage_Adminhtml_Block_Template
{
    /**
     * Block redirect type xml path
     */
    const XML_PATH_REDIRECT_TYPE_BLOCK = 'global/redirect_type_block/';

    /**
     * Config instance
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * Initializes config instance
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_config = !empty($args['config']) ? $args['config'] : Mage::getConfig();
        parent::__construct($args);
    }

    /**
     * Prepares "edit" block.
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $type = $this->getRequest()->getParam('type');
        if (!empty($type)) {
            $blockClassName = (string)$this->_config->getNode(self::XML_PATH_REDIRECT_TYPE_BLOCK . $type);
            if (!empty($blockClassName)) {
                $editBlock = $this->getLayout()->createBlock($blockClassName);
                $this->setChild('edit', $editBlock);
            }
        }

        return parent::_prepareLayout();
    }
}
