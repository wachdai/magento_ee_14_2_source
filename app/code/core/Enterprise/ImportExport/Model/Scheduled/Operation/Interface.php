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
 * @package     Enterprise_ImportExport
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Scheduled operation interface
 *
 * @category    Enterprise
 * @package     Enterprise_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
interface Enterprise_ImportExport_Model_Scheduled_Operation_Interface
{
    /**
     * Run operation through cron
     *
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return bool
     */
    function runSchedule(Enterprise_ImportExport_Model_Scheduled_Operation $operation);


    /**
     * Initialize operation model from scheduled operation
     *
     * @param Enterprise_ImportExport_Model_Scheduled_Operation $operation
     * @return object operation instance
     */
    function initialize(Enterprise_ImportExport_Model_Scheduled_Operation $operation);

    /**
     * Log debug data to file.
     *
     * @param mixed $debugData
     * @return object
     */
    function addLogComment($debugData);

    /**
     * Return human readable debug trace.
     *
     * @return array
     */
    function getFormatedLogTrace();
}
