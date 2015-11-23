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
 * @category    Varien
 * @package     Varien_Filter
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Template constructions parameters tokenizer
 *
 * @category   Varien
 * @package    Varien_Filter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
 
class Varien_Filter_Template_Tokenizer_Parameter extends Varien_Filter_Template_Tokenizer_Abstract
{
       
    /**
     * Tokenize string and return getted parameters
     *
     * @return array
     */
    public function tokenize() 
    {
        $parameters = array();
        $parameterName = '';
        while($this->next()) {
            if($this->isWhiteSpace()) {
                continue;
            } else if($this->char()!='=') {
                $parameterName .= $this->char();
            } else {
                $parameters[$parameterName] = $this->getValue();
                $parameterName = '';
            }
            
        }       
        return $parameters;
    }
    
    /**
     * Get string value in parameters througth tokenize
     * 
     * @return string
     */
    public function getValue() {
        $this->next();
        $value = '';
        if($this->isWhiteSpace()) { 
            return $value;
        }
        $quoteStart = $this->char() == "'" || $this->char() == '"';
        
        
        if($quoteStart) {
           $breakSymbol = $this->char();
        } else { 
           $breakSymbol = false;
           $value .= $this->char();
        }
          
        while ($this->next()) {
            if (!$breakSymbol && $this->isWhiteSpace()) {
                break;
            } else if ($breakSymbol && $this->char() == $breakSymbol) {
                break;
            } else if ($this->char() == '\\') {
                $this->next();
                $value .= $this->char();
            } else {
                $value .= $this->char();
            }                        
        }
        
        return $value;
    }
    
}
