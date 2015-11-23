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

class Enterprise_Support_Model_Backup_Item_Cmd_Bash extends Varien_Object
{
    /**
     * Script Name
     *
     * @var string
     */
    protected $_scriptName;

    /**
     * Script Interpreter
     *
     * @var string
     */
    protected $_scriptInterpreter;

    /**
     * @var string
     */
    protected $_redirectOutput;

    /**
     * Set Script Interpreter
     *
     * @param string $scriptInterpreter
     *
     * @return string
     */
    public function setScriptInterpreter($scriptInterpreter)
    {
        $this->_scriptInterpreter = $scriptInterpreter;
    }

    /**
     * Get Script Interpreter
     *
     * @return string
     */
    public function getScriptInterpreter()
    {
        return $this->_scriptInterpreter;
    }

    /**
     * Set output
     * Redirect output
     *
     * @param string $output
     */
    public function setRedirectOutput($output)
    {
        $this->_redirectOutput = $output;
    }

    /**
     * Get Output
     *
     * @return string
     */
    public function getRedirectOutput()
    {
        return $this->_redirectOutput;
    }

    /**
     * Generate command with arguments
     *
     * @param bool $argsWithKeys
     * @param string $equalSeparator
     *
     * @return string
     */
    public function generate($argsWithKeys = true, $equalSeparator = '=')
    {
        $data = $this->getData();
        $args = '';
        foreach ($data as $key => $value) {
            if ($argsWithKeys) {
                if ($value) {
                    $args .= sprintf(' --%s%s%s', $key, $equalSeparator, $value);
                } else {
                    $args .= sprintf(' --%s', $key);
                }
            } else {
                $args .= sprintf(' %s', $value);
            }
        }
        $cmd = $this->getScriptInterpreter() . Mage::helper('enterprise_support')->getScriptsPath()
            . $this->getScriptName() . $args;

        if ($this->_redirectOutput) {
            $cmd .= ' > ' . $this->_redirectOutput;
        }

        return $cmd;
    }

    /**
     * Set Script Name
     *
     * @param string $scriptName
     */
    public function setScriptName($scriptName)
    {
        $this->_scriptName = $scriptName;
    }

    /**
     * Get Script Name
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->_scriptName;
    }
}
