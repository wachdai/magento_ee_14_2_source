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
class Enterprise_UrlRewrite_Model_Url_Rewrite extends Mage_Core_Model_Abstract
    implements Mage_Core_Model_Url_Rewrite_Interface
{
    /**
     * Rewrite matchers path
     */
    const REWRITE_MATCHERS_PATH          = 'default/rewrite_matchers';

    const REWRITE_MATCHERS_PRIORITY_PATH = 'rewrite_matchers/%s/priority';

    const REWRITE_MATCHERS_TITLE_PATH    = 'rewrite_matchers/%s/title';

    const REWRITE_MATCHERS_MODEL_PATH    = 'rewrite_matchers/%s/model';

    /**
     * Mage Factory model
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        $this->_eventPrefix = 'enterprise_url_rewrite';
        $this->_eventObject = 'url_rewrite';
        parent::__construct($args);
    }

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('enterprise_urlrewrite/url_rewrite');
    }

    /**
     * Load url rewrite entity by request_path
     *
     * @param array $paths
     * @return Enterprise_UrlRewrite_Model_Url_Rewrite
     */
    public function loadByRequestPath($paths)
    {
        $this->setId(null);

        $rewriteRows = $this->_getResource()->getRewrites($paths);

        $matchers = $this->_factory->getSingleton('enterprise_urlrewrite/system_config_source_matcherPriority')
            ->getRewriteMatchers();

        foreach ($matchers as $matcherIndex) {
            $matcher = $this->_factory->getSingleton($this->_factory->getConfig()->getNode(
                sprintf(self::REWRITE_MATCHERS_MODEL_PATH, $matcherIndex), 'default'
            ));
            foreach ($rewriteRows as $row) {
                if ($matcher->match($row, $paths['request'])) {
                    $this->setData($row);
                    break(2);
                }
            }
        }
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

    /**
     * Check redirect type
     *
     * @param string $type
     * @return bool
     */
    public function hasOption($type)
    {
        return $this->getData('options') == $type;
    }
}
