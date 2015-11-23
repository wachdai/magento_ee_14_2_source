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
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma;

use Mage\Adminhtml\Test\Block\Widget\FormTabs;
use Enterprise\Rma\Test\Block\Adminhtml\Rma\Edit\Tab\Items;

/**
 * Rma tabs on view page.
 */
class Edit extends FormTabs
{
    /**
     * Locator for rma items grid.
     *
     * @var string
     */
    protected $rmaItemsGrid = '#rma_info_tabs_items_section_content';

    /**
     * Return rma items grid.
     *
     * @return Items
     */
    public function getItemsGrid()
    {
        return $this->blockFactory->create(
            '\Enterprise\Rma\Test\Block\Adminhtml\Rma\Edit\Tab\Items',
            ['element' => $this->_rootElement->find($this->rmaItemsGrid)]
        );
    }
}
