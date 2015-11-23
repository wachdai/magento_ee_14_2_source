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
 * @package     Enterprise_Search
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Search queries relations grid container
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Block_Adminhtml_Search_Edit extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Enable grid container
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'enterprise_search';
        $this->_controller = 'adminhtml_search';
        $this->_headerText = Mage::helper('enterprise_search')->__('Related Search Terms');
        $this->_addButtonLabel = Mage::helper('enterprise_search')->__('Add New Search Term');
        parent::__construct();
        $this->_removeButton('add');
    }

}
