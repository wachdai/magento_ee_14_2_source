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
 * Admin search test connection controller
 *
 * @category    Enterprise
 * @package     Enterprise_Search
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Adminhtml_Search_System_Config_TestconnectionController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check for connection to server
     */
    public function pingAction()
    {
        if (empty($_REQUEST['host']) || empty($_REQUEST['port']) || empty($_REQUEST['path'])) {
            echo 0;
            return;
        }

        $timeout = (isset($_REQUEST['timeout']) && $_REQUEST['timeout'] > 0)
            ? (float)$_REQUEST['timeout']
            : Enterprise_Search_Model_Adapter_Solr_Abstract::DEFAULT_TIMEOUT;

        $result = Mage::getResourceModel('enterprise_search/engine', array(
                'hostname' => $_REQUEST['host'],
                'port'     => (int)$_REQUEST['port'],
                'path'     => $_REQUEST['path'],
                'login'    => (isset($_REQUEST['login'])) ? $_REQUEST['login'] : '',
                'password' => (isset($_REQUEST['password'])) ? $_REQUEST['password'] : '',
                'timeout'  => $timeout))
            ->test();

        if ($result === false) {
            echo 0;
        } else {
            echo 1;
        }
    }
}
