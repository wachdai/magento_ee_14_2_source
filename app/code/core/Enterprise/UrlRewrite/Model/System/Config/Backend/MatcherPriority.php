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
 * UrlRewrite model
 *
 * @category    Enterprise
 * @package     Enterprise_UrlRewrite
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Model_System_Config_Backend_MatcherPriority extends Mage_Core_Model_Config_Data
{
    /**
     * Factory model
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Constructor with parameters.
     *
     * Array of arguments with keys:
     *  - '_collection' instance of Enterprise_UrlRewrite_Model_Matcher_Collection
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        parent::__construct();
    }

    /**
     * Load value
     *
     * @param mixed $id
     * @param mixed $field
     * @return Enterprise_UrlRewrite_Model_System_Config_Backend_MatcherPriority
     */
    public function load($id, $field=null)
    {
        $this->setValue($this->_factory->getSingleton('enterprise_urlrewrite/system_config_source_matcherPriority')
            ->getCurrentMatchersOrderValue());
        return $this;
    }

    /**
     * Save value
     *
     * @return Enterprise_UrlRewrite_Model_System_Config_Backend_MatcherPriority
     */
    public function save()
    {
        if ($this->getOldValue() !== $this->getValue()) {
            $matchers = explode('-', $this->getValue());
            $priority = 0;
            /** @var $configModel Mage_Core_Model_Config */
            $configModel = $this->_factory->getSingleton('core/config');
            foreach ($matchers as $matcher) {
                $priority += 10;
                $path = sprintf(Enterprise_UrlRewrite_Model_Url_Rewrite::REWRITE_MATCHERS_PRIORITY_PATH, $matcher);
                $configModel->saveConfig($path, $priority);
            }
        }
        return $this;
    }
}
