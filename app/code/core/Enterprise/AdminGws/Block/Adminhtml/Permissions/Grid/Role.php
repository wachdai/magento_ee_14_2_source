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
 * @package     Enterprise_AdminGws
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Admin roles grid
 *
 */
class Enterprise_AdminGws_Block_Adminhtml_Permissions_Grid_Role extends Mage_Adminhtml_Block_Permissions_Grid_Role
{
    /**
     * Add allowed websites/stores column
     *
     * @return Enterprise_Permissions_Block_Permissions_Grid_Role
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('gws', array(
            'header'   => $this->__('Allowed Scopes'),
            'width'    => '200',
            'sortable' => false,
            'filter'   => false,
            'renderer' => 'enterprise_admingws/adminhtml_permissions_grid_renderer_gws'
        ));

        return $this;
    }
}
