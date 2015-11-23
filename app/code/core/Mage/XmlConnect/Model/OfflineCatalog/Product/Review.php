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
 * @package     Mage_XmlConnect
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Xmlconnect offline catalog review model
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_OfflineCatalog_Product_Review extends Mage_XmlConnect_Model_OfflineCatalog_Abstract
{
    /**
     * Review url
     */
    const REVIEW_URL = 'xmlconnect/catalog/productreviews/id/%1$s/';

    /**
     * Return review block
     *
     * @param Mage_XmlConnect_Helper_OfflineCatalog $exportHelper
     * @return Mage_Core_Block_Abstract
     */
    public function getLayoutBlock($exportHelper)
    {
        return $exportHelper->getBlock('xmlconnect.catalog.product.reviews');
    }

    /**
     * Return review url
     *
     * @return mixed
     */
    protected function _getActionUrl()
    {
        return sprintf(Mage::getBaseUrl() . self::REVIEW_URL, $this->getProduct()->getId());
    }
}
