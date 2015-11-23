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
 * @category    Varien
 * @package     Varien_Db
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Sqlsrv driver DB Statement
 *
 * @category    Varien
 * @package     Varien_Db
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Db_Statement_Sqlsrv extends Zend_Db_Statement_Sqlsrv
{
    /**
     * Fetches a row from the result set.
     *
     * @param  int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param  int $cursor OPTIONAL Absolute, relative, or other.
     * @param  int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        try {
            $result = parent::fetch($style, $cursor, $offset);
        } catch (Zend_Db_Statement_Sqlsrv_Exception $e) {
            if ($e->getCode() == -22) {
                return false;
            }
            throw $e;
        }

        if ($result === null) {
            return false;
        }

        return $result;
    }
}
