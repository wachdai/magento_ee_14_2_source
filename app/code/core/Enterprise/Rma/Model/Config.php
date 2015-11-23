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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * RMA config
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Config extends Varien_Object
{
    /**
     * XML configuration paths
     */
    const XML_PATH_RMA_EMAIL                    = 'sales_email/enterprise_rma';
    const XML_PATH_AUTH_EMAIL                   = 'sales_email/enterprise_rma_auth';
    const XML_PATH_COMMENT_EMAIL                = 'sales_email/enterprise_rma_comment';
    const XML_PATH_CUSTOMER_COMMENT_EMAIL       = 'sales_email/enterprise_rma_customer_comment';

    const XML_PATH_EMAIL_ENABLED                = '/enabled';
    const XML_PATH_EMAIL_TEMPLATE               = '/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE         = '/guest_template';
    const XML_PATH_EMAIL_IDENTITY               = '/identity';
    const XML_PATH_EMAIL_COPY_TO                = '/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = '/copy_method';

    /**
     * XML configuration path for customer comments recipient
     */
    const XML_PATH_CUSTOMER_COMMENT_EMAIL_RECIPIENT = 'sales_email/enterprise_rma_customer_comment/recipient';

    /**
     * Current store instance
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store = null;

    /**
     * Current config root path
     *
     * @var string
     */
    protected $_configPath = null;


    /**
     * Initialize config object for default store and config root
     *
     * @param string $configRootPath Current config root
     * @param mixed $store Current store
     * @return Enterprise_Rma_Model_Config
     */
    public function init($configRootPath, $store)
    {
        $this->setStore($store);
        $this->setRootPath($configRootPath);

        return $this;
    }

    /**
     * Set config store
     *
     * @param mixed $store
     * @return Enterprise_Rma_Model_Config
     */
    public function setStore($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $this->_store = $store;
        } elseif ($store = intval($store)) {
            $this->_store = Mage::app()->getStore($store);
        } else {
            $this->_store = Mage::app()->getStore();
        }
        return $this;
    }

    /**
     * Retrieve store object
     *
     * @param mixed $store
     * @return Mage_Core_Model_Store
     */
    public function getStore($store = null)
    {
        if($store){
            if ($store instanceof Mage_Core_Model_Store) {
                return $store;
            } elseif (is_int($store)) {
                return Mage::app()->getStore($store);
            }
        } elseif (is_null($this->_store)) {
            $this->_store = Mage::app()->getStore();
        }
        return $this->_store;
    }

    /**
     * Set config root path
     *
     * @param string $path
     * @return Enterprise_Rma_Model_Config
     */
    public function setRootPath($path)
    {
        $this->_configPath = $path;
        return $this;
    }

    /**
     * Retrieve path from config root
     *
     * @param string $path
     * @return string
     */
    public function getRootPath($path = '')
    {
        return $this->_configPath . $path;
    }

    /**
     * Get root config path for RMA Emails
     *
     * @return string
     */
    public function getRootRmaEmail()
    {
        return self::XML_PATH_RMA_EMAIL;
    }

    /**
     * Get root config path for RMA Authorized Emails
     *
     * @return string
     */
    public function getRootAuthEmail()
    {
        return self::XML_PATH_AUTH_EMAIL;
    }

    /**
     * Get root config path for Admin Comment Emails
     *
     * @return string
     */
    public function getRootCommentEmail()
    {
        return self::XML_PATH_COMMENT_EMAIL;
    }

    /**
     * Get root config path for Customer Comment Emails
     *
     * @return string
     */
    public function getRootCustomerCommentEmail()
    {
        return self::XML_PATH_CUSTOMER_COMMENT_EMAIL;
    }

    /**
     * Get value of Enabled parameter for store
     *
     * @param string|null $path Root path for parameter
     * @param int|Mage_Core_Model_Store|null $store
     * @return mixed
     */
    public function isEnabled($path = null, $store = null)
    {
        return $this->_getConfig($path . self::XML_PATH_EMAIL_ENABLED, $store);
    }

    /**
     * Get array of emails from CopyTo parameter for store
     *
     * @param string|null $path Root path for parameter
     * @param int|Mage_Core_Model_Store|null $store
     * @return mixed
     */
    public function getCopyTo($path = '', $store = null)
    {
        $data = $this->_getConfig($path . self::XML_PATH_EMAIL_COPY_TO, $store);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * Get value of Copy Method parameter for store
     *
     * @param string|null $path Root path for parameter
     * @param int|Mage_Core_Model_Store|null $store
     * @return mixed
     */
    public function getCopyMethod($path = '', $store = null)
    {
        return $this->_getConfig($path . self::XML_PATH_EMAIL_COPY_METHOD, $store);
    }

    /**
     * Get value of Template for Guest parameter for store
     *
     * @param string|null $path Root path for parameter
     * @param int|Mage_Core_Model_Store|null $store
     * @return mixed
     */
    public function getGuestTemplate($path = '', $store = null)
    {
        return $this->_getConfig($path . self::XML_PATH_EMAIL_GUEST_TEMPLATE, $store);
    }

    /**
     * Get value of Template parameter for store
     *
     * @param string|null $path Root path for parameter
     * @param int|Mage_Core_Model_Store|null $store
     * @return mixed
     */
    public function getTemplate($path = '', $store = null)
    {
        return $this->_getConfig($path . self::XML_PATH_EMAIL_TEMPLATE, $store);
    }

    /**
     * Get value of Email Sender Identity parameter for store
     *
     * @param string|null $path Root path for parameter
     * @param int|Mage_Core_Model_Store|null $store
     * @return mixed
     */
    public function getIdentity($path = '', $store = null)
    {
        return $this->_getConfig($path . self::XML_PATH_EMAIL_IDENTITY, $store);
    }

    /**
     * Get absolute path for $path parameter
     *
     * @param  $path Absolute path or relative from initialized root
     * @return string
     */
    protected function _getPath($path)
    {
        if ($this->_configPath && strpos($path, $this->_configPath) !== false) {
            return $path;
        } else {
            return $this->getRootPath($path);
        }
    }

    /**
     * Get Store Config value for path
     *
     * @param string $path Path to config value. Absolute from root or Relative from initialized root
     * @param mixed $store
     * @return mixed
     */
    protected function _getConfig($path, $store)
    {
        if (is_null($store)) {
            $store = $this->_store;
        }
        return Mage::getStoreConfig($this->_getPath($path), $this->getStore($store));
    }

    /**
     * Get config value for customer comment's recipient.
     *
     * This config value doesn't fit the common canvas so there is this atom method for it
     *
     * @param mixed $store
     * @return mixed
     */
    public function getCustomerEmailRecipient($store)
    {
        $senderCode = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_COMMENT_EMAIL_RECIPIENT, $store);
        return Mage::getStoreConfig('trans_email/ident_' . $senderCode . '/email', $store);
    }
}
