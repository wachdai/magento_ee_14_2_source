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
 * UrlRewrite Helper
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get all possible 1 dimensional array permutations
     *
     * @param array $array
     * @param array $permutations
     * @return array
     */
    public function getVectorPermutations($array, $permutations = array())
    {
        $result = array();
        foreach($array as $key => $value) {
            $copy = $permutations;
            $copy[$key] = $value;
            $temp = array_diff_key($array, $copy);
            if (count($temp) == 0) {
                $result[] = $copy;
            } else {
                $result = array_merge($result, $this->getVectorPermutations($temp, $copy));
            }
        }
        return $result;
    }
}
