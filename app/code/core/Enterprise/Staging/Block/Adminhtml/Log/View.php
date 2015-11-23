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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Staging History Item View
 *
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Log_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_informationRenderers = array();

    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'enterprise_staging';
        $this->_controller = 'adminhtml_log';
        $this->_mode = 'view';

        $this->_headerText = Mage::helper('enterprise_staging')->__('Details');
        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');
    }

    public function getHeaderCssClass() {
        return 'icon-head head-staging-log';
    }

    public function addInformationRenderer($type, $block, $template)
    {
        $this->_informationRenderers[$type] = array(
            'block'     => $block,
            'template'  => $template,
            'renderer'  => null
        );
        return $this;
    }

    /**
     * Retrieve information renderer block
     *
     * @param string $type
     * @return Mage_Core_Block_Abstract
     */
    public function getInformationRenderer($type)
    {
        if (!isset($this->_informationRenderers[$type])) {
            $type = 'default';
        }
        if (is_null($this->_informationRenderers[$type]['renderer'])) {
            $this->_informationRenderers[$type]['renderer'] = $this->getLayout()
                ->createBlock($this->_informationRenderers[$type]['block'])
                ->setTemplate($this->_informationRenderers[$type]['template']);
        }
        return $this->_informationRenderers[$type]['renderer'];
    }

    public function getInformationHtml(Varien_Object $log)
    {
        return $this->getInformationRenderer($log->getAction())
            ->setLog($log)
            ->toHtml();
    }

    /**
     * Retrieve currently viewing log
     *
     * @return Enterprise_Staging_Model_Staging_Log
     */
    public function getLog()
    {
        if (!($this->getData('log') instanceof Enterprise_Staging_Model_Staging_Log)) {
            $this->setData('log', Mage::registry('log'));
        }
        return $this->getData('log');
    }

}
