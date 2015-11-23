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
class Enterprise_UrlRewrite_Model_System_Config_Source_MatcherPriority
    extends Mage_Core_Model_Config_Data
{
    /**
     * Mage Factory model
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Rewrite matchers order
     *
     * @var array
     */
    protected $_matchersOrder = array();

    /**
     * Constructor with parameters.
     *
     * Array of arguments with keys:
     *  - 'factory' instance of Mage_Core_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        parent::__construct();
    }

    /**
     * Convert matchers array to order value
     *
     * @param array $matchers
     * @return array
     */
    protected function _toOrderValuesArray(array $matchers)
    {
        foreach ($matchers as $key => $value) {
            $matchers[$key] = implode('-', $value);
        }
        return $matchers;
    }

    /**
     * Convert matcher array to select options labels
     *
     * @param array $matchers
     * @return array
     */
    protected function _toOrderLabelsArray(array $matchers)
    {
        foreach ($matchers as $key => $value) {
            array_walk($value, array($this, '_matcherIndexToTitle'));
            $matchers[$key] = implode(' - ', $value);
        }
        return $matchers;
    }

    /**
     * Convert matcher index to title
     *
     * @param $index
     */
    protected function _matcherIndexToTitle(&$index)
    {
        $path = sprintf(Enterprise_UrlRewrite_Model_Url_Rewrite::REWRITE_MATCHERS_TITLE_PATH, $index);
        $title = $this->_factory->getConfig()->getNode($path, 'default');
        $index = $this->_factory->getHelper('enterprise_urlrewrite')->__(ucfirst($title ? (string) $title : $index));
    }

    /**
     * Convert possible matchers permutations to options array
     *
     * @param array $matchers
     * @return array
     */
    protected function _toOptions(array $matchers)
    {
        return array_combine(
            $this->_toOrderValuesArray($matchers),
            $this->_toOrderLabelsArray($matchers)
        );
    }

    /**
     * Fetch options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = array();
        $permutations = $this->_factory
            ->getHelper('enterprise_urlrewrite')
            ->getVectorPermutations($this->getRewriteMatchers());
        foreach ($this->_toOptions($permutations) as $value => $label) {
            $result[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $result;
    }

    /**
     * Get current matchers order value
     *
     * @return string
     */
    public function getCurrentMatchersOrderValue()
    {
        return implode('-', $this->getRewriteMatchers());
    }

    /**
     * Fetch rewrite matchers names sorted by priority
     *
     * @return mixed
     *
     * @throws Mage_Core_Exception
     */
    public function getRewriteMatchers()
    {
        if (!empty($this->_matchersOrder)) {
            return $this->_matchersOrder;
        }
        $nodes = $this->_factory->getConfig()->getNode(Enterprise_UrlRewrite_Model_Url_Rewrite::REWRITE_MATCHERS_PATH);
        foreach ($nodes->children() as $node) {
            $priority = (int)$node->priority;
            if (isset($this->_matchersOrder[$priority])) {
                throw new Mage_Core_Exception(
                    $this->_factory->getHelper('enterprise_urlrewrite')->__(
                        'URL matcher "%s" has same priority as "%s".',
                        $node->getName(),
                        $this->_matchersOrder[$priority]
                    )
                );
            }
            $this->_matchersOrder[$priority] = $node->getName();
        }
        ksort($this->_matchersOrder);
        return $this->_matchersOrder;
    }
}
