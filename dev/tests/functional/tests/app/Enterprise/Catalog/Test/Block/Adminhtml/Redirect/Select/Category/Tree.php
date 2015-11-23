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

namespace Enterprise\Catalog\Test\Block\Adminhtml\Redirect\Select\Category;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Select category block.
 */
class Tree extends Block
{
    /**
     * Skip Category Selection button selector.
     *
     * @var string
     */
    protected $skipCategoryButton = '#skip_category';

    /**
     * Category link selector.
     *
     * @var string
     */
    protected $categoryLink = "//a/span[contains(text(),'%s')]";

    /**
     * Select categories by name.
     *
     * @param mixed $categories
     * @return void
     */
    public function selectCategory($categories)
    {
        $categories === null
            ? $this->skipCategorySelection()
            : $this->_rootElement->find(sprintf($this->categoryLink, $categories[0]), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Skip category selection.
     *
     * @return void
     */
    protected function skipCategorySelection()
    {
        $this->_rootElement->find($this->skipCategoryButton)->click();
    }
}
