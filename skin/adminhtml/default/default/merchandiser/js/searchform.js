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
 * @category    design
 * @package     default_default
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
var searchJS = Class.create();
searchJS.prototype = {
    initialize : function(){
        
    },
    submitSearch : function(form){
        var query = $('search').value;
        this.sendAjax(form.action+'?name='+query, form.method, 'search-results');
    },
    sendAjax : function(action,method,output_id){
        if (!method) { 
            method = 'post'; 
        }
        if (!output_id) {
            output_id = 'searchRequestResult';
        }
        var req = new Ajax.Request(action, {
            'method' : method,
            'onSuccess' : function(transport) {
                var searchjs = new searchJS();
                searchjs.hideLoader();
                $(output_id).update(transport.responseText);
                var merJSObj = new merchandiserJS();
                merJSObj.hideDuplicates();
                merJSObj.observeCategoryAdd();
                affectResultedProducts();
            },
            'onFailure' : function(transport) {
                var searchjs = new searchJS();
                searchjs.hideLoader();
                $(output_id).update(transport.responseText);
            }
        });
        this.showLoader();
        $(output_id).childElements().each( function(item) {
            item.remove();
        });
    },
    loadFeature : function(action){
        var req = new Ajax.Request(action, {
            method : 'post',
            parameters: {
                show: 'intro'
            },
            onSuccess : function(transport) {
                var searchjs = new searchJS();
                searchjs.hideLoader();
                $('featureInfo').update(transport.responseText);
            }
        });
        this.showLoader();
    },
    showLoader : function(sMaskId) {
        if (!sMaskId) {
            sMaskId = 'loading-mask';
        }
        $(sMaskId).style.display = 'block';
    },
    hideLoader : function() {
        $('loading-mask').style.display = 'none';
    }
}