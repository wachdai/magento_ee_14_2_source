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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Support_Helper_Html extends Mage_Core_Helper_Abstract
{
    /**
     * Normalize, format and construct HTML table cell text for Magento grid block(s)
     *
     * @param string $text
     * @param string $rawText
     *
     * @return string
     */
    public function getGridCellHtml($text, $rawText)
    {
        $text = $this->_prepareCellHtml($text);

        $cellCss = $this->_getCellClassByValue($rawText);
        return $cellCss !== null ? '<span class="cell-value-' . $cellCss . '">' . $text . '</span>' : $text;
    }

    /**
     * Normalize, format and construct HTML table cell text
     *
     * @param string $text
     * @param string $rawText
     * @param string $cellId
     *
     * @return string
     */
    public function getExportTableCellHtml($text, $rawText, $cellId)
    {
        $text = $this->_prepareCellHtml($text);

        $maxLength = Enterprise_Support_Helper_Data::SYSREPORT_DATA_MAX_NONE_COLLAPSIBLE_CELL_STRING_LENGTH;
        $isTextLengthMustBeCut = mb_strlen($rawText, 'UTF-8') > $maxLength;
        if ($isTextLengthMustBeCut) {
            $fullText = mb_substr($text, 0, $maxLength, 'UTF-8');
            $fullText .= '<a href="javascript:void(0)" onclick="showFullText(\'cell_' . $cellId . '\')"> ... More</a>';
            $fullText .='<div class="report-cell-text">' . $text . '</div>';

            $text = $fullText;
        }

        $cellCss = $this->_getCellClassByValue($rawText);
        $html = '<td'. ($isTextLengthMustBeCut ? ' id="cell_' . $cellId . '"' : '') .
            ($cellCss !== null ? ' class="' . $cellCss . '"' : '') .'>'. $text . '</td>';

        return $html;
    }

    /**
     * Prepare HTML for text value of table cell
     *
     * @param string $text
     *
     * @return string
     */
    protected function _prepareCellHtml($text)
    {
        $text = htmlspecialchars($text);
        $text = $this->_replaceLeadingSpacesWithNoneBreakSpaces($text);
        $text = $this->_prepareHtmlForFilePathStrings($text);
        $text = $this->_prepareHtmlForDiffStrings($text);
        $text = str_replace(array("\n", "\r"), '<br />', $text);

        return $text;
    }

    /**
     * Replace the leading spaces with &nbsp; with same number of times
     *
     * @param string $text
     *
     * @return string
     */
    protected function _replaceLeadingSpacesWithNoneBreakSpaces($text)
    {
        $originalLength = mb_strlen($text, 'UTF-8');
        $text = ltrim($text, ' ');
        $newLength = mb_strlen($text, 'UTF-8');
        return str_repeat('&nbsp;', $originalLength - $newLength) . $text;
    }

    /**
     * Replace special {text} constructions with styled HTML
     *
     * @param $string
     *
     * @return string|bool
     */
    protected function _prepareHtmlForFilePathStrings($string)
    {
        $string = preg_replace('~\{\{([^}]+)\}\}~is', '[[[\1]]]', $string);
        $string = preg_replace('~\{([^{}]+)\}~is', '<span class="file-path">\1</span>', $string);
        return preg_replace('~\[\[\[([^]]+)\]\]\]~is', '{{\1}}', $string);
    }

    /**
     * Replace special (diff: +-<digit>) constructions with styled HTML
     *
     * @param $string
     *
     * @return string|bool
     */
    protected function _prepareHtmlForDiffStrings($string)
    {
        $string = preg_replace('~\(diff: (\-[^\)]+)\)~is', '<span class="diff-negative">(\1)</span>', $string);
        return preg_replace('~\(diff: (\+[^\)]+)\)~is', '<span class="diff-positive">(\1)</span>', $string);
    }

    /**
     * Get table cell css class depending on its specific value
     * Used in HTML format report
     *
     * @param mixed $value
     *
     * @return null|string
     */
    protected function _getCellClassByValue($value)
    {
        $yesValues = array('Yes', 'Enabled', 'Ready', 'Exists', 'success');
        $processValues = array('Processing', 'Invalidated', 'running', 'pending', 'Scheduled');
        $noValues = array('No', 'Disabled', 'Reindex Required', 'Missing', 'error');

        $class = null;
        if ((in_array($value, $yesValues) && !empty($value))) {
            $class = 'flag-yes';
        } else if (in_array($value, $processValues) && !empty($value)) {
            $class = 'flag-processing';
        } else if (in_array($value, $noValues) && !empty($value)) {
            $class = 'flag-no';
        }

        return $class;
    }
}
