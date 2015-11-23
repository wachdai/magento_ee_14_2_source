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

var AdminCheckout = new Class.create();
AdminCheckout.prototype = {
    initialize: function(data)
    {
        this.checkboxes     = $H({});
        this.loadBaseUrl    = false;
        this.gridProducts   = $H({});
        this.quoteAddFields = {};
        this.sourceGrids    = {};

        this.actionUrls     = data.action_urls ? data.action_urls : {};
        this.messages       = data.messages ? data.messages : {};
        this.customerId     = data.customer_id ? data.customer_id : false;
        this.storeId        = data.store_id ? data.store_id : false;
        this.currencySymbol = data.currency_symbol ? data.currency_symbol : '';
        this.productPriceBase = {};
        this.sourceNoClean  = $H({});
    },

    getActionUrl: function (action)
    {
        return this.actionUrls[action];
    },

    getMessage: function (id)
    {
        return this.messages[id];
    },

    setLoadBaseUrl : function(url)
    {
        this.loadBaseUrl = url;
    },

    setCurrencySymbol : function(symbol){
        this.currencySymbol = symbol;
    },

    onAjaxSuccess: function(transport)
    {
        if (transport.responseText.isJSON()) {
            var response = transport.responseText.evalJSON()
            if (response.error) {
                throw response;
            } else if (response.url) {
                setLocation(response.url);
             }
        }
    },

    getContext: function(elementName)
    {
        return (this.checkboxes.get(elementName) || this.checkboxes.set(elementName, $H({})));
    },

    resetSource: function()
    {
        this.checkboxes =  $H({});
        $('products_search_accordion').select('input.checkbox').each(function(e) {
            if (e.checked) {
                e.checked = false;
                this.setCheckboxChecked(e, false);
            }
        }.bind(this));
    },

    reloadItems: function(container)
    {
        new Ajax.Request(this.getActionUrl('cart'),
            {
                onSuccess: function(transport) {
                    try {
                        this.onAjaxSuccess(transport);
                        $('checkout_items').update(transport.responseText);
                    } catch (e) {
                        var message = e.error ? e.error : e.message;
                        alert(message);
                    }
                }.bind(this)
            });
    },

    searchProducts: function()
    {
        productsGrid.reloadParams = {source: Object.toJSON(this.checkboxes)};
        productsGrid.doFilter();
    },

    addToCart: function()
    {
        this.productGridAddSelected();
    },

    updateItems: function(auxiliaryParams)
    {
        this.itemsUpdate(auxiliaryParams);
    },

    applyCoupon: function(ccode)
    {
        if (ccode == undefined || !ccode.blank()) {
            ccode = ccode == undefined ? '' : ccode;
            new Ajax.Request(this.getActionUrl('applyCoupon'),
                {
                    parameters: {code: ccode},
                    onSuccess: function(transport) {
                        try {
                            this.onAjaxSuccess(transport);
                            this.reloadItems();
                            $('coupon_container').update(transport.responseText);
                        } catch (e) {
                            var message = e.error ? e.error : e.message;
                            alert(message);
                        }
                    }.bind(this)
                });
       }
    },

    reloadCoupon: function()
    {
        new Ajax.Request(this.getActionUrl('coupon'),
            {
                onSuccess: function(transport) {
                    try {
                        this.onAjaxSuccess(transport);
                        $('coupon_container').update(transport.responseText);
                    } catch (e) {
                        var message = e.error ? e.error : e.message;
                        alert(message);
                    }
                }.bind(this)
            });
    },

    /**
    * Left for compatibility of js.
    * Adviced to use productGridRowClick() for more powerful product management.
    */
    gridRowClick: function(grid, event)
    {
        var trElement = Event.findElement(event, 'tr');
        var isInput = Event.element(event).tagName.toLowerCase() == 'input';
        if (trElement) {
            var checkbox = Element.select(trElement, 'input');
            if (checkbox[0]) {
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                grid.setCheckboxChecked(checkbox[0], checked);
            }
        }
    },

    /**
    * Left for compatibility of js.
    * Adviced to use productGridCheckboxCheck() for more powerful product management.
    */
    gridCheckboxCheck: function(grid, element, checked)
    {
        this.setCheckboxChecked(element, checked);
    },

    setCheckboxChecked: function(element, checked)
    {
        if (checked) {
            var qty = 1;
            if(element.qty) {
                element.qty.disabled = false;
                if (!element.qty.value) {
                    element.qty.value = 1;
                }
                qty = element.qty.value;
                this.getContext(element.name).set(element.value, qty);
            }
        } else {
            if(element.qty){
                element.qty.disabled = true;
            }
            this.getContext(element.name).unset(element.value);
        }
    },

    /**
     * Left for compatibility of js.
     * Adviced to use productGridRowInit() for more powerful product management.
     */
    gridRowInit: function(grid, row)
    {
        var checkbox = $(row).down('input[type="checkbox"]');
        if(!checkbox) {
            return;
        }
        var context = this.getContext(checkbox.name);
        var found = false;
        if (context && context.get(checkbox.value)) {
            checkbox.checked = true;
            found = true;
        }
        var qty = $(row).down('.input-text');
        if(!qty) {
            return;
        }
        checkbox.qty = qty;
        qty.checkboxElement = checkbox;
        qty.disabled = true;
        if (found) {
            qty.disabled = false;
            qty.value = context.get(checkbox.value);
        }
        Event.observe(qty,'keyup', this.qtyChanged.bind(this));
        Event.observe(qty,'change',this.qtyChanged.bind(this));
    },

    qtyChanged : function(event){
        var element = Event.element(event);
        if (element && element.checkboxElement && element.checkboxElement.checked){
            this.getContext(element.checkboxElement.name).set(element.checkboxElement.value, element.value);
        }
    },

    serializeData: function(container)
    {
        var fields = $(container).select('input', 'select');
        var data = Form.serializeElements(fields, true);
        return $H(data);
    },

    prepareParams : function(params){
        if (!params) {
            params = {};
        }
        if (!params.customer) {
            params.customer = this.customerId;
        }
        if (!params.store) {
            params.store = this.storeId;
        }
        if (!params.form_key) {
            params.form_key = FORM_KEY;
        }

        return params;
    },

    addSourceGrid: function (info) {
        this.sourceGrids[info.htmlId] = info;
        this.saveDefaultQtysInSource(info.htmlId);
    },

    productConfigureSubmit: function(listType, area, fieldsPrepare, itemsFilter) {
        // prepare loading areas and build url
        this.loadingAreas = area;
        var url = this.loadBaseUrl + 'block/' + area + '?isAjax=true';

        // prepare additional fields
        fieldsPrepare = this.prepareParams(fieldsPrepare);
        fieldsPrepare.json = 1;

        // create fields
        var fields = [];
        for (var name in fieldsPrepare) {
            fields.push(new Element('input', {type: 'hidden', name: name, value: fieldsPrepare[name]}));
        }
        productConfigure.addFields(fields);

        // filter items
        productConfigure.itemsFilter = $H({});
        if (itemsFilter) {
            if (itemsFilter instanceof Hash) {
                itemsFilter.each(function (item) {
                    productConfigure.addItemsFilter(item.key, item.value);
                });
            } else {
                productConfigure.addItemsFilter(listType, itemsFilter);
            }
        }

        // configure and run submitting
        if (listType instanceof Array) {
            var clearSources = true;
            var listType = productConfigure.addComplexListType(listType, url);
        } else {
            var clearSources = false;
            productConfigure.addListType(listType, {urlSubmit: url});
        }
        productConfigure.setOnLoadIFrameCallback(listType, function(response) {
            if (clearSources) {
                this.loadAreaResponseHandler(response);
                this.clearSources();
            } else {
                setLocation(window.location.href);
            }
        }.bind(this));

        productConfigure.submit(listType);
    },

    productGridAddSelected: function (additionalFields) {
        if (!additionalFields) {
            additionalFields = [];
        }
        if (typeof additionalFields == 'string') {
            additionalFields = [additionalFields];
        }
        // Scan through all grids and add checked products them to submitted list types
        var sources = $H({});
        for (var sourceId in this.sourceGrids) {
            var table = $(sourceId + '_table');
            if (!table) {
                continue;
            }

            var items = [];
            var elements = table.select('input[type=checkbox][name=' + sourceId + ']:checked');
            if (!elements.length) {
                // Try to get another type of inputs: maybe it's a dynamic table?
                elements = table.select('input[type=text][name=' + sourceId + ']');
            }
            elements.each(function (elem) {
                if (!elem.value || (elem.value == 'on')) {
                    return;
                }
                var itemInfo = {};
                itemInfo.id = elem.value;

                var tr = elem.up('tr');
                if (tr) {
                    var qty = tr.select('input[name=qty]');
                    if (qty.length) {
                        itemInfo.qty = qty[0].value;
                    }
                    for (var i = 0; i < additionalFields.length; i++) {
                        var field = tr.select('input[name="' + additionalFields[i] + '"]');
                        if (field.length) {
                            itemInfo[additionalFields[i]] = field[0].value;
                        }
                    }
                }

                items.push(itemInfo);
            });

            if (!items.length) {
                continue;
            }
            sources.set(sourceId, items);
        }
        if (!sources.values().length) {
            alert(this.getMessage('chooseProducts'));
            return;
        }

        // Prepare additional fields and filtered items
        var fieldsPrepare = {};
        var itemsFilter = $H({});
        var listTypes = [];
        sources.each(function (item) {
            var sourceId = item.key;
            var items = item.value;
            var sourceGrid = this.sourceGrids[sourceId];
            var listType = sourceGrid.listType;

            var itemIds = [];
            for (var i = 0, len = items.length; i < len; i++) {
                var item = items[i];
                itemIds.push(item.id);

                var paramKey = 'list[' + listType + '][item][' + item.id + '][qty]';
                fieldsPrepare[paramKey] = item.qty;
                for (var j = 0; j < additionalFields.length; j++) {
                    var fieldName = additionalFields[j];
                    paramKey = 'list[' + listType + '][item][' + item.id + '][' + fieldName + ']';
                    fieldsPrepare[paramKey] = item[fieldName] ? item[fieldName] : '';
                }

                if (!productConfigure.itemConfigured(listType, item.id)) {
                    var paramKey = 'list[' + listType + '][item][' + item.id + '][_config_absent]';
                    fieldsPrepare[paramKey] = 1;
                }
            }
            itemsFilter.set(listType, itemIds);

            listTypes.push(listType);
        }.bind(this));
        this.productConfigureSubmit(listTypes, ['items', 'errors'], fieldsPrepare, itemsFilter);
    },

    productGridRowClick: function(grid, event){
        var trElement = Event.findElement(event, 'tr');
        var qtyElement = trElement.select('input[name="qty"]')[0];
        var eventElement = Event.element(event);
        var isInputCheckbox = eventElement.tagName == 'INPUT' && eventElement.type == 'checkbox';
        var isInputQty = eventElement.tagName == 'INPUT' && eventElement.name == 'qty';
        if (trElement && !isInputQty) {
            var checkbox = Element.select(trElement, 'input[type="checkbox"]')[0];
            var confLink = Element.select(trElement, 'a')[0];
            var priceCol = Element.select(trElement, '.price')[0];
            if (checkbox) {
                // processing non composite product
                if (confLink.readAttribute('disabled')) {
                    var checked = isInputCheckbox ? checkbox.checked : !checkbox.checked;
                    grid.setCheckboxChecked(checkbox, checked);
                // processing composite product
                } else if (isInputCheckbox && !checkbox.checked) {
                    grid.setCheckboxChecked(checkbox, false);
                // processing composite product
                } else if (!isInputCheckbox || (isInputCheckbox && checkbox.checked)) {
                    var listType = confLink.readAttribute('list_type');
                    var itemId = confLink.readAttribute('item_id');
                    if (!itemId) {
                        itemId = confLink.readAttribute('product_id');
                    }
                    if (typeof this.productPriceBase[itemId] == 'undefined') {
                        var priceBase = 0;
                        if( (typeof priceCol != 'undefined') && (priceCol)) {
                            priceBase = priceCol.innerHTML.match(/.*?([0-9\.,]+)/);
                        }
                        if (priceBase && (priceBase.length >= 1)) {
                            priceBase = priceBase[1].replace(/,/g, '');
                        } else {
                            priceBase = 0;
                        }
                        if (!priceBase) {
                            this.productPriceBase[itemId] = 0;
                        } else {
                            this.productPriceBase[itemId] = parseFloat(priceBase);
                        }
                    }
                    productConfigure.setConfirmCallback(listType, function() {
                        // sync qty of popup and qty of grid
                        var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                        if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                            qtyElement.value = confirmedCurrentQty.value;
                        }
                        // calc and set product price
                        var productPrice = parseFloat(this._calcProductPrice() + this.productPriceBase[itemId]);
                        if(typeof priceCol != 'undefined' && priceCol) {
                            priceCol.innerHTML = this.currencySymbol + productPrice.toFixed(2);
                        }
                        // and set checkbox checked
                        grid.setCheckboxChecked(checkbox, true);
                    }.bind(this));
                    productConfigure.setCancelCallback(listType, function() {
                        if (!$(productConfigure.confirmedCurrentId) || !$(productConfigure.confirmedCurrentId).innerHTML) {
                            grid.setCheckboxChecked(checkbox, false);
                        }
                    });
                    productConfigure.setShowWindowCallback(listType, function() {
                        // sync qty of grid and qty of popup
                        var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                        if (formCurrentQty && qtyElement && (qtyElement.value != '') && !isNaN(qtyElement.value)) {
                            var value = qtyElement.value;
                            if (value == '0') {
                                value = 1;
                            }
                            formCurrentQty.value = value;
                        }
                    }.bind(this));
                    productConfigure.showItemConfiguration(listType, itemId);
                }
            }
        }
    },

    /**
     * Calc product price through its options
     */
    _calcProductPrice: function () {
        var productPrice = 0;
        var optQty = 1;
        var getPriceFields = function (elms) {
            var productPrice = 0;
            var getPrice = function (elm) {
                var optQty = 1;
                if (elm.hasAttribute('qtyId')) {
                    if (!$(elm.getAttribute('qtyId')).value) {
                        return 0;
                    } else {
                        optQty = parseFloat($(elm.getAttribute('qtyId')).value);
                    }
                }
                if (elm.hasAttribute('price') && !elm.disabled) {
                    return parseFloat(elm.readAttribute('price')) * optQty;
                }
                return 0;
            };
            for(var i = 0; i < elms.length; i++) {
                if (elms[i].type == 'select-one' || elms[i].type == 'select-multiple') {
                    for(var ii = 0; ii < elms[i].options.length; ii++) {
                        if (elms[i].options[ii].selected) {
                            productPrice += getPrice(elms[i].options[ii]);
                        }
                    }
                }
                else if (((elms[i].type == 'checkbox' || elms[i].type == 'radio') && elms[i].checked)
                        || ((elms[i].type == 'file' || elms[i].type == 'text' || elms[i].type == 'textarea' || elms[i].type == 'hidden')
                            && Form.Element.getValue(elms[i]))
                ) {
                    productPrice += getPrice(elms[i]);
                }
            }
            return productPrice;
        }.bind(this);
        productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('input'));
        productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('select'));
        productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('textarea'));
        return productPrice;
    },

    productGridRowInit : function(grid, row){
        var checkbox = $(row).select('.checkbox')[0];
        var inputs = $(row).select('.input-text');
        if (checkbox && inputs.length > 0) {
            checkbox.inputElements = inputs;
            for (var i = 0; i < inputs.length; i++) {
                var input = inputs[i];
                input.checkboxElement = checkbox;

                var product = this.gridProducts.get(checkbox.value);
                if (product) {
                    var defaultValue = product[input.name];
                    if (defaultValue) {
                        if (input.name == 'giftmessage') {
                            input.checked = true;
                        } else {
                            input.value = defaultValue;
                        }
                    }
                }

                input.disabled = !checkbox.checked || input.hasClassName('input-inactive');

                Event.observe(input,'keyup', this.productGridRowInputChange.bind(this));
                Event.observe(input,'change',this.productGridRowInputChange.bind(this));
            }
        }
    },

    productGridRowInputChange : function(event){
        var element = Event.element(event);
        if (element && element.checkboxElement && element.checkboxElement.checked){
            if (element.name!='giftmessage' || element.checked) {
                this.gridProducts.get(element.checkboxElement.value)[element.name] = element.value;
            } else if (element.name=='giftmessage' && this.gridProducts.get(element.checkboxElement.value)[element.name]) {
                delete(this.gridProducts.get(element.checkboxElement.value)[element.name]);
            }
        }
    },

    productGridCheckboxCheck : function(grid, element, checked){
        if (checked) {
            if (element.inputElements) {
                this.gridProducts.set(element.value, {});
                var product = this.gridProducts.get(element.value);
                for (var i = 0; i < element.inputElements.length; i++) {
                    var input = element.inputElements[i];
                    if (!input.hasClassName('input-inactive')) {
                        input.disabled = false;
                        if (input.name == 'qty' && !input.value) {
                            input.value = 1;
                        }
                    }

                    if (input.checked || input.name != 'giftmessage') {
                        product[input.name] = input.value;
                    } else if (product[input.name]) {
                        delete(product[input.name]);
                    }
                }
            }
        } else {
            if (element.inputElements) {
                for(var i = 0; i < element.inputElements.length; i++) {
                    element.inputElements[i].disabled = true;
                }
            }
            this.gridProducts.unset(element.value);
        }
        grid.reloadParams = {'products[]':this.gridProducts.keys()};
    },

    loadAreaResponseHandler : function (response){
        if (response.error) {
            alert(response.message);
        }
        if(response.ajaxExpired && response.ajaxRedirect) {
            setLocation(response.ajaxRedirect);
        }
        if (!this.loadingAreas) {
            this.loadingAreas = [];
        }
        if (typeof this.loadingAreas == 'string') {
            this.loadingAreas = [this.loadingAreas];
        }
        if (this.loadingAreas.indexOf('message') == -1) {
            this.loadingAreas.push('message');
        }
        for (var i=0; i < this.loadingAreas.length; i++) {
            var id = this.loadingAreas[i];
            if($(this.getAreaId(id))) {
                if ('message' == id || response[id]) {
                    $(this.getAreaId(id)).update(response[id] ? response[id] : '');
                }
            }
        }
    },

    getAreaId : function(area){
        return 'checkout_' + area;
    },

    itemsUpdate : function(auxiliaryParams) {
        var area = ['items'];
        // prepare additional fields
        var fieldsPrepare = {update_items: 1};
        var info = $('order-items_grid').select('input', 'select', 'textarea');
        for (var i = 0; i < info.length; i++) {
            if(!info[i].disabled && (info[i].type != 'checkbox' || info[i].checked)) {
                fieldsPrepare[info[i].name] = info[i].getValue();
            }
        }
        if (auxiliaryParams instanceof Object) {
            for (var paramName in auxiliaryParams) {
                fieldsPrepare[paramName] = String(auxiliaryParams[paramName]);
            }
        }
        fieldsPrepare = Object.extend(fieldsPrepare, this.quoteAddFields);
        this.productConfigureSubmit('checkoutItemsGrid', area, fieldsPrepare);
        this.quoteAddFields = {};
    },

    showQuoteItemConfiguration: function(itemId){
        var listType    = 'checkoutItemsGrid';
        var qtyElement  = $('order-items_grid').select('input[name="item\['+itemId+'\]\[qty\]"]')[0];
        productConfigure.setConfirmCallback(listType, function() {
            // sync qty of popup and qty of grid
            var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
            if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                qtyElement.value = confirmedCurrentQty.value;
            }
            this.quoteAddFields['item['+itemId+'][configured]'] = 1;
        }.bind(this));

        productConfigure.setShowWindowCallback(listType, function() {
            // sync qty of grid and qty of popup
            var formCurrentQty = productConfigure.getCurrentFormQtyElement();
            if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                formCurrentQty.value = qtyElement.value;
            }
        }.bind(this));

        checkoutItemsGridcartControl.configureItem(itemId);
    },

    clearSources: function () {
        for (var sourceId in this.sourceGrids) {
            if (this.sourceNoClean.get(sourceId)) {
                continue;
            }
            var sourceGrid = this.sourceGrids[sourceId];
            var table = $(sourceId + '_table');
            if (!table) {
                continue;
            }

            var items = [];
            table.select('input[type=checkbox][name=' + sourceId + ']').each(function(elem) {
                elem.checked = false;
            });

            table.select('input[type=text][name=qty]').each(function(elem) {
                elem.value = elem.defaultValue || '';
            });
        }
    },

    saveDefaultQtysInSource: function (sourceId) {
        var sourceGrid = this.sourceGrids[sourceId];
        var table = $(sourceId + '_table');
        if (!table) {
            return;
        }

        table.select('input[type=text][name=qty]').each(function(elem) {
            elem.defaultValue = elem.value;
        });
    },

    /**
     * Prevents grid inputs to be cleared after successful submission
     * @param sourceId
     */
    addNoCleanSource: function (sourceId)
    {
        this.sourceNoClean.set(sourceId, true);
    }
};
