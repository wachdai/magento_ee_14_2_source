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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Gift registry search results
 *
 * @category   Enterprise
 * @package    Enterprise_GiftRegistry
 */
class Enterprise_GiftRegistry_Block_Search_Results extends Mage_Core_Block_Template
{
    /**
     * Set search results and create html pager block
     */
    public function setSearchResults($results)
    {
        $this->setData('search_results', $results);
        $pager = $this->getLayout()->createBlock('page/html_pager', 'giftregistry.search.pager')
            ->setCollection($results)->setIsOutputRequired(false);
        $this->setChild('pager', $pager);
    }

    /**
     * Return frontend registry link
     *
     * @param Enterprise_GiftRegistry_Model_Entity $item
     * @return string
     */
    public function getRegistryLink($item)
    {
        return $this->getUrl('*/view/index', array('id' => $item->getUrlKey()));
    }

    /**
     * Retrieve item formated date
     *
     * @param Enterprise_GiftRegistry_Model_Entity $item
     * @return string
     */
    public function getFormattedDate($item)
    {
        if ($item->getEventDate()) {
            return $this->formatDate($item->getEventDate(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        }
    }
}
