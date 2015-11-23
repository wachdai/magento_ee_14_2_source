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


/**
 * Staging merge setting block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Merge extends Mage_Adminhtml_Block_Widget
{
    /**
     * merge settinggs blocks
     *
     * @var array
     */
    private $_mergeSettingsBlock = array();

    /**
    * merge settings template
    *
    * @var string
    */
    private $_mergeSettingsBlockDefaultTemplate = 'enterprise/staging/merge/settings.phtml';

    /**
     * merge settings block types
     *
     * @var array
     */
    private $_mergeSettingsBlockTypes = array();

    /**
     * Retrieve currently edited staging object
     *
     * @return Enterprise_Staging_Block_Manage_Staging
     */
    public function getStaging()
    {
        if (!($this->getData('staging') instanceof Enterprise_Staging_Model_Staging)) {
            $this->setData('staging', Mage::registry('staging'));
        }
        return $this->getData('staging');
    }

    /**
     * get merge setting bclocks
     *
     * @param string $stagingType
     * @return array
     */
    protected function _getMergeSettingsBlock($stagingType)
    {
        if (!isset($this->_mergeSettingsBlock[$stagingType])) {
            $block = 'enterprise_staging/staging_merge_settings';
            if (isset($this->_mergeSettingsBlockTypes[$stagingType])) {
                if ($this->_mergeSettingsBlockTypes[$stagingType]['block'] != '') {
                    $block = $this->_mergeSettingsBlockTypes[$stagingType]['block'];
                }
            }
            $this->_mergeSettingsBlock[$stagingType] = $this->getLayout()->createBlock($block);
        }
        return $this->_mergeSettingsBlock[$stagingType];
    }

    /**
     * get merge settinh block template
     *
     * @param string $stagingType
     * @return array
     */
    protected function _getMergeSettingsBlockTemplate($stagingType)
    {
        if (isset($this->_mergeSettingsBlockTypes[$stagingType])) {
            if ($this->_mergeSettingsBlockTypes[$stagingType]['template'] != '') {
                return $this->_mergeSettingsBlockTypes[$stagingType]['template'];
            }
        }
        return $this->_mergeSettingsBlockTypes;
    }

    /**
     * Returns staging merge settings block html
     *
     * @param Mage_Catalog_Model_Product $staging
     * @param boolean $displayMinimalPrice
     */
    public function getMergeSettingsHtml($staging = null, $idSuffix='')
    {
        if (is_null($staging)) {
            $staging = $this->getStaging();
        }
        if (!$staging->getType()) {
            $staging->setType('website');
        }
        return $this->_getMergeSettingsBlock($staging->getType())
            ->setTemplate($this->_getMergeSettingsBlockTemplate($staging->getType()))
            ->setStaging($staging)
            ->setIdSuffix($idSuffix)
            ->toHtml();
    }

    /**
     * return html structure of merge
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getMergeSettingsHtml();
    }

    /**
     * Adding customized merge settings block for staging type
     *
     * @param string $type
     * @param string $block
     * @param string $template
     */
    public function addMergeSettingsBlockType($type, $block = '', $template = '')
    {
        if ($type) {
            $this->_mergeSettingsBlockTypes[$type] = array(
                'block'     => $block,
                'template'  => $template
            );
        }
    }
}
