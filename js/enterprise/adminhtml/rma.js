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
var AdminRma = new Class.create();
AdminRma.prototype = {
    initialize : function(data){
        this.newRmaItemId           = 0;
        this.loadAttributesUrl      = false;
        this.loadBundleUrl          = false;
        this.loadOrderId            = false;
        this.loadSplitLineUrl       = false;
        this.loadShippingMethodsUrl = false;
        this.loadPslUrl             = false;
        this.deleteLineLabel        = false;
        this.windowMask             = $('popup-window-mask');
        this.bundleArray            = new Object();
        this.formId                 = false;
        this.shippingMethod         = false;
        this.gridProducts           = $H({});
        this.grid                   = false;
        this.reloadResponder        = new Object({onComplete: this.doAddSelectedProduct.bind(this)});
    },

    getRowIdByClick : function(event) {
        var trElement = $(event.srcElement) == undefined ? Event.findElement(event, 'tr')
            : $(event.srcElement).up('tr');
        this.formId = trElement.up('form').id;
        return trElement.down('input.rowId').value;
    },

    itemDetailsRowClick : function(event,forceDivId){
        var divId;
        if (forceDivId) {
            divId = event;
        } else {
            var itemId = this.getRowIdByClick(event);
            divId = 'itemDiv_' + itemId;
        }
        if (!$(divId)) {
            this.getAjaxData(itemId);
        } else {
            this.showPopup(divId);
        }
    },

    itemReasonOtherRowClick: function(event){
        var divId = 'rma_reason_container';
        var itemId = this.getRowIdByClick(event);
        Event.observe($(divId).down('button.ok_button'), 'click', function(){$('rma_reason_container').hide();$('popup-window-mask').hide()});
        $$('input[type="hidden"]').each(function(element){
            if (element.name == 'items[' + itemId + '][reason_other]') {
                $(divId).down('div#rma_reason_content').innerHTML = element.value.escapeHTML();
            }
        });
        this.showPopup(divId);
    },

    getLoadAttributesLink: function(itemId) {
        return this.loadAttributesUrl + 'item_id/' + itemId + '?isAjax=true';
    },

    nameTransformation: function(element, itemId) {
        //We must provide the following transformation
        //name  -> item[9][name]
        //name[key]  ->  item[9][name][key]
        var arrayDivider = element.name.indexOf('[');
        if (arrayDivider == -1) {
            arrayDivider = element.name.length;
        }

        return 'items[' + itemId + '][' + element.name.slice(0,arrayDivider) + ']' + element.name.slice(arrayDivider);
    },

    getAjaxData : function(itemId) {
        var url = this.getLoadAttributesLink(itemId);

        new Ajax.Request(url, {
            onSuccess: function(transport) {
                var response = transport.responseText;
                var divId = 'itemDiv_' + itemId;
                this.addPopupDiv(response, divId, itemId);
                var realThis = this;
                $(divId).descendants().each(function(element){
                    if ((element.tagName.toLowerCase() == 'input') || (element.tagName.toLowerCase() == 'select') || (element.tagName.toLowerCase() == 'textarea')) {
                        if ((element.tagName.toLowerCase() == 'input') && (element.type == 'file')) {
                            element.name = element.name + '_' + itemId;
                        } else {
                            element.name = realThis.nameTransformation(element, itemId);
                        }
                    }
                });

                this.addPopupDivButtonsBindings(divId);
                this.showPopup(divId);
            }.bind(this)
        });
    },

    addPopupDiv: function(response, divId, itemId) {
        var parentTd = $('h' + itemId) ? $('h' + itemId).up('td') : $('id_' + itemId).childElements().last();
        parentTd.insert({
            top: new Element('div', {id: divId}).update(response).addClassName('rma-popup')
        });
    },

    addPopupDivButtonsBindings: function(divId) {
        Event.observe($(divId).down('button.ok_button'), 'click', this.okButtonClick.bind(this, divId));
        Event.observe($(divId).down('button.cancel_button'), 'click', this.cancelButtonClick.bind(this));
    },

    okButtonClick: function(divId) {
        var itemId = divId.replace('itemDiv_', ''),
            parentTd = $('h' + itemId) ? $('h' + itemId).up('td') : $('id_'+itemId).childElements().last();

        parentTd.descendants().each(function(element) {
            if (element.hasClassName('attrsValues')) {
                element.remove()
            }
        })
        this.hidePopups();
    },

    cancelButtonClick: function(itemId) {
        this.hidePopups();
    },

    showPopup: function(divId) {
        $(divId).show().setStyle({
            'marginTop': -$(divId).getDimensions().height / 2 + 'px'
        });
        $('popup-window-mask').setStyle({
            height: $('html-body').getHeight() + 'px'
        }).show();
    },

    hidePopups: function() {
        $('details_container').childElements().each(Element.hide);
        $$('.rma-popup').each(Element.hide);
        $('popup-window-mask').hide();
    },

    itemSplitLineRowClick : function(event) {
        var itemId = this.getRowIdByClick(event),
            url = this.loadSplitLineUrl + 'item_id/' + itemId + '?isAjax=true',
            divId = 'grid_result_' + itemId;

        if ($(divId)) {
            this.splitLine(this, divId, itemId)
        } else {
            new Ajax.Request(url, {
                onSuccess: function(transport) {
                    var response = transport.responseText;
                    $('details_container').insert({
                        top: new Element('div', {id: divId, style: 'display:none'}).update(response)
                    });
                    $(divId).select('input[type="file"]').each(function(file) {
                        file.name = file.name + '_' + itemId;
                    });
                    this.splitLine(this, divId, itemId);
                }.bind(this)
            });
        }
    },

    splitLine: function(obj, divId, itemId) {
        var hiddenForItem = $('rma_info_tabs_items_section_content').select('#h' + itemId)[0];
        if (!hiddenForItem) {
            return false;
        }

        var timeSuffix = new Date().getTime(),
            trSplit = hiddenForItem.up('tr'),
            trAdded = new Element('tr', {id: 'new_tr_' + timeSuffix,
                'class': trSplit.hasClassName('even') ? 'pointer' : 'pointer even'
            });

        trSplit.id = 'old_tr_' + timeSuffix;

        // replace <script to avoid evalScripts() execution, <tr>.innerHTML assignment does not work in IE8 properly
        var escapedHTML = $(divId).down('tbody').down('tr').innerHTML.replace(/<(\/?)script/g, '&lt;$1script');
        trSplit.update(escapedHTML);
        trSplit.insert({after: trAdded.insert(escapedHTML)});

        trAdded.descendants().each(function(element){
            if (element.tagName.toLowerCase() == 'input' || element.tagName.toLowerCase() == 'select') {
                element.name = element.name.replace('[' + itemId + ']', '[' + itemId + '_' + timeSuffix + ']');
                if (element.id == 'h' + itemId) {
                    element.id = 'h' + itemId + '_' + timeSuffix;
                }
                if (element.type != 'hidden') {
                    element.value = '';
                }
            }
            if (element.tagName.toLowerCase() == 'a' && element.hasClassName('item_split_line')) {
                var deleteLink = new Element('a', {href: '#', 'class': 'item_delete_line'}).update(obj.deleteLineLabel);
                element.replace(deleteLink);
            }
        })

        var detailsDivId = 'itemDiv_' + itemId;
        var newDetailsDivId = detailsDivId + '_' + timeSuffix;
        if (!$(detailsDivId)) {
            var url = this.getLoadAttributesLink(itemId);

            new Ajax.Request(url, {
                onSuccess: function(transport) {
                    var response = transport.responseText;
                    this.addPopupDiv(response, detailsDivId, itemId);
                    this.hidePopups();
                    var realThis = this;
                    $(detailsDivId).descendants().each(function(element){
                        if (element.tagName.toLowerCase() == 'input'
                            || element.tagName.toLowerCase() == 'select'
                            || element.tagName.toLowerCase() == 'textarea'
                        ) {
                            if (!(element.tagName.toLowerCase() == 'input' && element.type == 'file')) {
                                element.name = realThis.nameTransformation(element, itemId);
                            }
                        }
                    });
                    this.copyDetailsData(detailsDivId, newDetailsDivId, itemId, timeSuffix);
                    $(detailsDivId).select('input[type="file"]').each(function(file) {
                        file.name = file.name + '_' + itemId;
                    });
                    $(newDetailsDivId).select('input[type="file"]').each(function(file) {
                        file.name = file.name + '_' + itemId + '_' + timeSuffix;
                    });
                    this.addPopupDivButtonsBindings(detailsDivId);
                    this.addPopupDivButtonsBindings(newDetailsDivId);
                }.bind(this)
            });
        } else {
            this.copyDetailsData(detailsDivId, newDetailsDivId, itemId, timeSuffix);
            $(newDetailsDivId).select('input[type="file"]').each(function(file) {
                file.name = file.name + '_' + itemId + '_' + timeSuffix;
            })
        }

        Event.observe(trSplit.down('a.item_details'), 'click', this.itemDetailsRowClick.bind(this));
        Event.observe(trSplit.down('a.item_split_line'), 'click', this.itemSplitLineRowClick.bind(this));
        Event.observe(trAdded.down('a.item_details'), 'click', this.itemDetailsRowClick.bind(this, newDetailsDivId));
        Event.observe(trAdded.down('a.item_delete_line'), 'click', this.deleteRowById.bind(this, trAdded.id, newDetailsDivId));

        var obj = this;
        $$('select.reason').findAll(function(obj) {
           return (obj.name == 'items[' + itemId + '][reason]'
               || obj.name == 'items[' + itemId + '_' + timeSuffix + '][reason]');
        }).each(function (elem) {
            obj.showOtherOption(elem);
            Event.observe(elem, 'change', obj.showOtherOption.bind(obj, elem));
        });
    },

    copyDetailsData: function(detailsDivId, newDetailsDivId, itemId, timeSuffix) {
        var parentTd = $('h' + itemId) ? $('h' + itemId + '_' + timeSuffix).up('td')
            : $('id_'+itemId + '_' + timeSuffix).childElements().last(),
            newDiv = new Element('div', {id: newDetailsDivId, 'class': $(detailsDivId).className});

        newDiv.innerHTML = $(detailsDivId).innerHTML; // not update() to avoid evalScripts() execution
        parentTd.insert({top: newDiv});

        $(newDetailsDivId).descendants().each(function(element){
            if ((element.tagName.toLowerCase() == 'input') || (element.tagName.toLowerCase() == 'select')) {
                element.name = element.name.replace('[' + itemId + ']', '[' + itemId + '_' + timeSuffix + ']');
                if (element.type != 'hidden') {
                    element.value = '';
                }
            }
        })

        this.addPopupDivButtonsBindings(detailsDivId);
        this.addPopupDivButtonsBindings(newDetailsDivId);
        this.okButtonClick(newDetailsDivId)
    },

    deleteRowById : function(rowId, divId){
        $(rowId).remove();
        if ($(divId)) {
            $(divId).remove();
        }
    },

    setLoadAttributesUrl : function(url){
        this.loadAttributesUrl  = url;
    },

    setLoadBundleUrl : function(url){
        this.loadBundleUrl      = url;
    },

    setLoadSplitLineUrl : function(url){
        this.loadSplitLineUrl = url;
    },

    setDeleteLineLabel : function(label){
        this.deleteLineLabel = label;
    },

    setLoadOrderId : function(id){
        this.loadOrderId = id;
    },

    addProduct : function(event){
        Ajax.Responders.unregister(this.reloadResponder);
        this.gridProducts = $H({});
        this.grid.reloadParams = {'products[]':this.gridProducts.keys()};
        Element.hide('rma-items-block');
        Element.show('select-order-items-block');
    },

    addSelectedProduct : function(event) {
        Ajax.Responders.register(this.reloadResponder);
        this.grid.resetFilter();
    },

    doAddSelectedProduct : function(event) {
        var items = $$('#order_items_grid_table .checkbox');
        var selected_items = [];
        var _rma = this;
        items.each(function(e) {
            if (e.type == 'checkbox' && e.checked == true) {
                selected_items.push(e);
            }
        });
        var tableRma = $('rma_items_grid_table');
        var tableRmaBody = tableRma.down('tbody.newRma');
        var className = true;
        if (!tableRmaBody) {
            tableRmaBody = tableRma.down('tbody');
            tableRmaBody.hide();
        } else {
            className = !tableRmaBody.childElements().last().hasClassName('even');
        }
        if (selected_items.length) {
            selected_items.each(function(e){
                if (e.type == 'checkbox' && e.value) {
                    _rma.addOrderItemToRmaGrid(e, className);
                    className = !className;
                    e.checked = false;
                }
            });
        }

        Element.hide('select-order-items-block');
        Element.show('rma-items-block');
    },

    addOrderItemToRmaGrid : function (idElement, className) {
        if(!idElement) return false;

        if (this.bundleArray[idElement.value] !== undefined) {
            var obj = this.bundleArray[idElement.value];
            for(var key in obj) {
                this.addOrderItemToGrid(obj[key], className);
            }
        } else {
            var orderItem = this.getOrderItem(idElement);
            this.addOrderItemToGrid(orderItem, className);
        }
    },

    addOrderItemToGrid: function (orderItem, className) {
        var fieldsProduct = [
            'product_name',
            'product_sku',
            'qty_ordered',
            'qty_requested',
            'reason',
            'condition',
            'resolution'
        ];
        var tableRma = $('rma_items_grid_table');

        var newRmaItemId = this.newRmaItemId

        var tbody = tableRma.down('tbody.newRma');
        if (!tbody) {
            tbody = new Element('tbody').addClassName('newRma');
        }

        var row = new Element('tr', {id: 'id_' + newRmaItemId, 'class': className ? 'even' : 'odd'});

        fieldsProduct.each(function(el,i) {
            var column = new Element('td');
            var data = '';
            if (orderItem[el]) {
                data = orderItem[el];
            } else {
                data = $('rma_properties_' + el);
                if (data) {
                    data = $(data).cloneNode(true);
                    data.name = 'items[' + newRmaItemId + '][' + data.name + ']';
                    data.id   = data.id + '_' + newRmaItemId;
                    data.addClassName('required-entry');
                }
            }
            column.insert(data);
            //adding reason other
            if (el == 'reason') {
                Event.observe($(data), 'change', rma.reasonChanged.bind(rma));
                var data_other = $('rma_properties_reason_other');
                data_other = $(data_other).cloneNode(true);
                data_other.name   = 'items[' + newRmaItemId + '][' + data_other.name + ']';
                data_other.setStyle({display:'none'});
                data_other.disabled = 'disabled';
                column.insert(data_other)
            }
            row.insert(column);
        });
        var column = new Element('td');
        var deleteLink = new Element('a', {href:'#'});
        Event.observe(deleteLink, 'click', this.deleteRow.bind(this));
        deleteLink.insert($$('label[for="rma_properties_delete_link"]').first().innerHTML);
        column.insert(deleteLink);
        column.insert('<span class="separator">|</span>');

        var detailsLink = new Element('a', {href:'#'});
        Event.observe(detailsLink, 'click', this.addDetails.bind(this));
        detailsLink.insert($$('label[for="rma_properties_add_details_link"]').first().innerHTML);
        column.insert(detailsLink);
        column.insert('<input type="hidden" name="items[' + this.newRmaItemId + '][order_item_id]" value="'+orderItem['item_id']+'"/>');
        row.insert(column);
        tableRma.insert(tbody.insert(row));
        this.newRmaItemId++;
    },

    addProductRowCallback: function(grid, event) {
        var trElement = Event.findElement(event, 'tr');
        var isInput = Event.element(event).tagName == 'INPUT';
        if (trElement) {
            var checkbox = Element.select(trElement, 'input');
            if (checkbox[0]) {
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                grid.setCheckboxChecked(checkbox[0], checked);
            }
            var link = Element.select(trElement, 'a[class="product_to_add"]');
            if (link[0]) {
                rma.showBundleItems(event)
            }
        }
    },

    addProductCheckboxCheckCallback: function(grid, element, checked){
        if (checked) {
            this.gridProducts.set(element.value, {});
        } else {
            this.gridProducts.unset(element.value);
        }
        grid.reloadParams = {'products[]':this.gridProducts.keys()};
        this.grid = grid;
    },

    reasonChanged: function(event) {
        var select = event.findElement('select')
        if (select.value === 'other') {
            select.next('input').show();
            select.next('input').disabled = '';
        } else {
            select.next('input').hide();
            select.next('input').disabled = 'disabled';
        }
    },

    getOrderItem: function (idElement) {
        var data = Array();
        var rowOrder = idElement.up(1);
        data['item_id'] = idElement.value;
        data['product_name'] = rowOrder.down(2).innerHTML;
        data['product_sku'] = rowOrder.down(3).innerHTML;
        data['qty_ordered'] = rowOrder.down(5).innerHTML;
        return data;
    },

    deleteRow: function (event) {
        var tableRmaBody = event.findElement('a').up(2);
        event.findElement('a').up(1).remove();
        this.bundleArray = new Object();
        if (!tableRmaBody.down()) {
            tableRmaBody.remove();
            $('rma_items_grid_table').down('tbody').show();
        }
    },

    addDetails: function (event) {
        var tr      = event.findElement('a').up(1);
        var itemId  = tr.id.split('_')[1];
        var divId   = 'itemDiv_' + itemId;

        if (!$(divId)) {
            var itemCalculated = $$('input[name="items[' + itemId + '][order_item_id]"]')[0].value;
            this.loadAttributesUrl = this.loadAttributesUrl + 'product_id/' + itemCalculated + '/';
            this.getAjaxData(itemId);
        } else {
            this.showPopup(divId);
        }
    },

    showBundleItems : function(event){
        var trElement = Event.findElement(event, 'tr');

        if (!trElement) {
            return false;
        }
        var checkbox = Element.select(trElement, 'input');
        if (checkbox[0]) {
            var itemId = checkbox[0].value;
        }

        if (trElement.select(':checked').length <= 0) {
            if (this.bundleArray[itemId]) {
                delete this.bundleArray[itemId];
            }
            return false;
        }

        var divId   = 'bundleDiv_' + itemId;
        var orderId = this.loadOrderId;
        if (!$(divId)) {
            this.getBundleAjaxData(itemId, orderId);
        } else {
            this.showBundlePopup(divId);
        }
    },

    getBundleAjaxData : function(itemId, orderId) {
        var url = this.loadBundleUrl + 'item_id/' + itemId + '/order_id/' + orderId + '?isAjax=true';

        new Ajax.Request(url, {
            onSuccess: function(transport) {
                var response = transport.responseText;
                this.addBundlePopupDiv(response, itemId);
            }.bind(this)
        });
    },

    addBundlePopupDiv: function(response, itemId){
        var divId = 'bundleDiv_' + itemId;
        $('details_container').insert({
            top: new Element('div', {id: divId}).update(response)
        });
        $(divId).addClassName('rma-popup');

        Event.observe($('rma_bundle_cancel_button_'+itemId), 'click', this.bundlePreviousStateReturns.bind(this, itemId));
        Event.observe($('rma_bundle_ok_button_'+itemId), 'click', this.bundleStoreState.bind(this, itemId));
        Event.observe($('all_items_'+itemId), 'click', this.checkAllItems.bind(this, itemId));

        var a = this;
        $$('.checkbox_rma_bundle_item_'+itemId).each(function(cb){
            Event.observe(cb, 'click', a.checkIndividualItems.bind(a, itemId));
        });
        this.showPopup(divId);
    },

    showBundlePopup: function(divId) {
        var itemId = divId.split('_')[1];

        $$('.checkbox_rma_bundle_item_'+itemId).each(function(checkbox) {
            checkbox.checked = false;
        });
        if (this.bundleArray[itemId]) {
            var obj = this.bundleArray[itemId];
            for(var key in obj) {
                for(var k in obj[key]) {
                    var cb = obj[key];
                    if (k == 'item_id') {
                        $('checkbox_rma_bundle_item_id_'+itemId+'_'+cb[k]).checked = "checked";
                    }
                }
            }
        }

        this.showPopup(divId);
    },

    bundleStoreState: function(itemId) {
        var parent  = itemId;
        var ba      = new Object();
        var i       = 0;

        $$('.checkbox_rma_bundle_item_'+itemId).each(function(checkbox) {
            if (checkbox.checked) {
                var child   = checkbox.value;
                var name    = $('checkbox_rma_bundle_item_name_'+parent+'_'+child).value;
                var sku     = $('checkbox_rma_bundle_item_sku_'+parent+'_'+child).value;
                var qty     = $('checkbox_rma_bundle_item_qty_'+parent+'_'+child).value;
                ba[i] = {
                    'item_id': child,
                    'product_name': name,
                    'product_sku': sku,
                    'qty_ordered': qty
                };
                i++;
            }
        });
        this.bundleArray[itemId] = ba;

        if (i > 0) {
            $$('input[value="'+itemId+'"]')[0].checked = "checked";
        } else {
            $$('input[value="'+itemId+'"]')[0].checked = false;
            delete this.bundleArray[itemId];
            this.addProductCheckboxCheckCallback(this.grid, $$('input[value="'+itemId+'"]')[0], false)
        }

        this.hidePopups();
    },

    bundlePreviousStateReturns: function(itemId) {
        if (this.bundleArray[itemId] !== undefined) {
            $$('input[value="'+itemId+'"]')[0].checked = "checked";
        } else {
            $$('input[value="'+itemId+'"]')[0].checked = false;
            this.addProductCheckboxCheckCallback(this.grid, $$('input[value="'+itemId+'"]')[0], false)
        }

        this.hidePopups();
    },

    checkAllItems: function(itemId) {
        $$('.checkbox_rma_bundle_item_'+itemId).each(function(checkbox) {
            checkbox.checked = "checked";
        });
    },

    checkIndividualItems: function(itemId) {
        $('individual_items_'+itemId).checked = "checked";
    },

    setLoadShippingMethodsUrl: function(url) {
        this.loadShippingMethodsUrl  = url;
    },

    setLoadPslUrl: function(url) {
        this.loadPslUrl  = url;
    },

    showShippingMethods: function() {
        var url         = this.loadShippingMethodsUrl;
        var parentDiv   = $('get-psl');
        var divId       = 'get-shipping-method';

        if ($(divId)) {
            this.showPopup(divId);
        } else {
            new Ajax.Request(url, {
                onSuccess: function(transport) {
                    var response = transport.responseText;
                    parentDiv.insert({
                        after: new Element('div', {id: divId}).update(response).addClassName('rma-popup')
                    });
                    this.showPopup(divId);
                    Event.observe($('get-shipping-method-cancel-button'), 'click', this.shippingMethodsCancelButton.bind(this));
                    Event.observe($('get-shipping-method-ok-button'), 'click', this.shippingMethodsCancelButton.bind(this));

                    var this_rma = this;
                    $$('input[id^="s_method_"]').each(function(element){
                        Event.observe(element, 'click', this_rma.showLabelPopup.bind(this_rma, element.value));
                    });
                }.bind(this)
            });
        }
    },

    shippingMethodsCancelButton: function() {
        this.hidePopups();
    },

    showLabelPopup: function(method) {
        this.hidePopups();
        var url = this.loadPslUrl + 'method/' + method + '?isAjax=true';
        new Ajax.Request(url, {
            onSuccess: function(transport) {
                var response = transport.responseText;
                $('get-psl').update(response);
                this.showWindow(method);
            }.bind(this)
        });
    },

    cancelPack: function() {
        packaging.cancelPackaging();
        this.showShippingMethods();
    },

    showWindow: function(method) {
        url = this.loadPslUrl + 'method/' + method + '/data/1?isAjax=true';
        var rma = this;
        new Ajax.Request(url, {
            onSuccess: function(transport) {
                var response = transport.responseText.isJSON() ? transport.responseText.evalJSON()
                    : transport.responseText;

                packaging = new Packaging(response);
                packaging.showWindow();

                this.shippingMethod = $('h_method_'+method).cleanWhitespace().innerHTML.evalJSON();

                packaging.paramsCreateLabelRequest['code']          = this.shippingMethod.Code;
                packaging.paramsCreateLabelRequest['carrier_title'] = this.shippingMethod.CarrierTitle;
                packaging.paramsCreateLabelRequest['method_title']  = this.shippingMethod.MethodTitle;
                packaging.paramsCreateLabelRequest['price']         = this.shippingMethod.PriceOriginal;

                packaging.setConfirmPackagingCallback(function(){
                    packaging.sendCreateLabelRequest();
                });
                packaging.setLabelCreatedCallback(function(response){
                    setLocation(packaging.thisPage);
                });

                $('package_template').insert({
                    after: new Element('div', {id: 'shipping_information'}).insert(packaging.shippingInformation)
                });

                $('get-shipping-method-carrier-title').insert(this.shippingMethod.CarrierTitle);
                $('get-shipping-method-method-title').insert(this.shippingMethod.MethodTitle);
                $('get-shipping-method-shipping-price').insert(this.shippingMethod.Price);

                Event.observe($('get-shipping-method-show-shipping-methods'), 'click', rma.cancelPack.bind(rma));
            }
        });
    },

    showOtherOption: function(element)
    {
        var inputEl = element.next('input[type="text"]');
        if (element.value == 'other') {
            if(inputEl) {
                inputEl.enable();
                inputEl.show();
            }
        } else {
            if(inputEl) {
                inputEl.hide();
                inputEl.disable();
            }
        }
    }
}
