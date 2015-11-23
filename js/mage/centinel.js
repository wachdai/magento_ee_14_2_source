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
 * @category    Mage
 * @package     js
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
var CentinelAuthenticate = Class.create();
CentinelAuthenticate.prototype = {
    initialize : function(blockId, iframeId)
    {
        this._isAuthenticationStarted = false;
        this._relatedBlocks = new Array();
        this.centinelBlockId = blockId;
        this.iframeId = iframeId;
        if (this._isCentinelBlocksLoaded()) {
            $(this.centinelBlockId).hide();
        }
    },

    isAuthenticationStarted : function()
    {
        return this._isAuthenticationStarted;
    },

    addRelatedBlock : function(blockId)
    {
        this._relatedBlocks[this._relatedBlocks.size()] = blockId;
    },

    _hideRelatedBlocks : function()
    {
        for (var i = 0; i < this._relatedBlocks.size(); i++) {
            $(this._relatedBlocks[i]).hide();
        }
    },

    _showRelatedBlocks : function()
    {
        for (var i = 0; i < this._relatedBlocks.size(); i++) {
            $(this._relatedBlocks[i]).show();
        }
    },

    _isRelatedBlocksLoaded : function()
    {
        for (var i = 0; i < this._relatedBlocks.size(); i++) {
            if(!$(this._relatedBlocks[i])) {
                return false;
            }
        }
        return true;
    },

    _isCentinelBlocksLoaded : function()
    {
        if(!$(this.centinelBlockId) || !$(this.iframeId)) {
            return false;
        }
        return true;
    },

    start : function(authenticateUrl)
    {
        if (this._isRelatedBlocksLoaded() && this._isCentinelBlocksLoaded()) {
            this._hideRelatedBlocks();
            $(this.iframeId).src = authenticateUrl;
            $(this.centinelBlockId).show();
            this._isAuthenticationStarted = true;
        }
    },

    success : function()
    {
        if (this._isRelatedBlocksLoaded() && this._isCentinelBlocksLoaded()) {
            this._showRelatedBlocks();
            $(this.centinelBlockId).hide();
            this._isAuthenticationStarted = false;
        }
    },

    cancel : function()
    {
        if (this._isAuthenticationStarted) {
            if (this._isRelatedBlocksLoaded()) {
                this._showRelatedBlocks();
            }
            if (this._isCentinelBlocksLoaded()) {
                $(this.centinelBlockId).hide();
                $(this.iframeId).src = '';
            }
            this._isAuthenticationStarted = false;
        }
    }
};
