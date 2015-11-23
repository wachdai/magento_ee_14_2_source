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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

abstract class Enterprise_Staging_Model_Staging_Mapper_Abstract extends Varien_Object
{
    /**
     * Staging instance id
     *
     * @var mixed
     */
    protected $_staging;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->_read  = Mage::getSingleton('core/resource')->getConnection('staging_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('staging_write');
    }

    /**
     * Declare staging instance
     *
     * @param   Enterprise_Staging_Model_Staging $staging
     * @return  Enterprise_Staging_Model_Staging_Mapper_Abstract
     */
    public function setStaging($staging)
    {
        $this->_staging = $staging;

        return $this;
    }

    /**
     * Retrieve staging object
     *
     * @param Enterprise_Staging_Model_Staging $staging
     * @return Enterprise_Staging_Model_Staging
     */
    public function getStaging()
    {
        if ($this->_staging instanceof Enterprise_Staging_Model_Staging) {
            return $this->_staging;
        } elseif (is_null($this->_staging)) {
            $_staging = Mage::registry('staging');
            if ($_staging && $this->_staging &&  ($_staging->getId() == $this->_staging)) {
                $this->_staging = $_staging;
            } else {
                if (is_int($this->_staging)) {
                    $this->_staging = Mage::getModel('enterprise_staging/staging')
                        ->load($this->_staging);
                } else {
                    $this->_staging = false;
                }
            }
        }

        return $this->_staging;
    }
}
