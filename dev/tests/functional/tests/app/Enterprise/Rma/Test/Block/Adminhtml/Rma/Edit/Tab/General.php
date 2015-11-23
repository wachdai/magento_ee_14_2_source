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
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma\Edit\Tab;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * General information tab on rma edit page(backend).
 */
class General extends \Mage\Adminhtml\Test\Block\Widget\Tab
{
    /**
     * Locator for comment list.
     *
     * @var string
     */
    protected $commentHistory = 'ul.note-list li';

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $context = $element ? $element : $this->_rootElement;
        return array_merge($this->getRequestDetails($context), ['comment' => $this->getCommentData()]);
    }

    /**
     * Return request details.
     *
     * @param SimpleElement $context
     * @return array
     */
    protected function getRequestDetails(SimpleElement $context)
    {
        $mapping = $this->dataMapping();
        $mappingDetails = $mapping['details']['value'];
        $data = [];

        unset($mappingDetails['composite']);
        foreach ($mappingDetails as $fieldName => $locator) {
            $element = $context->find($locator['selector'], $locator['strategy']);
            if ($element->isVisible()) {
                $data[$fieldName] = trim($element->getText());
            }
        }

        if (isset($data['entity_id'])) {
            $data['entity_id'] = str_replace('#', '', $data['entity_id']);
        }
        if (isset($data['order_id'])) {
            $data['order_id'] = str_replace('#', '', $data['order_id']);
        }

        return $data;
    }

    /**
     * Return comments data.
     *
     * @return array
     */
    protected function getCommentData()
    {
        $comments = $this->_rootElement->getElements($this->commentHistory);
        $data = [];

        foreach ($comments as $comment) {
            preg_match('@\n.*\n(.*)@', $comment->getText(), $matches);
            $data[] = ['comment' => $matches[1]];
        }

        return $data;
    }
}
