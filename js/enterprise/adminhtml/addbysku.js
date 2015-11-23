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

/**
 * Add By SKU class
 *
 * @method submitConfigured()
 * @method removeAllFailed()
 */
var AddBySku = Class.create();
AddBySku.prototype = {
    /**
     * Constructor
     *
     * @param order            Instance of AdminOrder
     * @param data             Array (see initialize())
     */
    initialize : function (order, data)
    {
        if (!data) data = {};
        this.lastId = 0;
        this.configuredSkus = [];
        this.configurableItems = {};
        this.dataContainerId = data.dataContainerId;
        this.deleteButtonHtml = data.deleteButtonHtml;
        this.order = order;
        this.listType = data.listType;
        this.errorGridId = data.errorGridId;
        this.fileFieldName = data.fileFieldName;
        this.fileUploadUrl = data.fileUploadUrl;
        this.fileUploadedParamName = data.fileUploaded;

        // Changing original productConfigure object for SKU items needs
        productConfigure.skuObject = this;
        var originConfiguredCheck = productConfigure.itemConfigured;
        productConfigure.itemConfigured = function (listType, itemId)
        {
            if (listType != this.skuObject.listType) {
                return originConfiguredCheck.apply(this, [listType, itemId]);
            }

            var indexOfItemId = this.skuObject.configuredSkus.indexOf(itemId);
            if (indexOfItemId != -1) {
                if (!originConfiguredCheck.apply(this, [listType, itemId])) {
                    this.skuObject.configuredSkus.splice(indexOfItemId, 1);
                    return false;
                }
                return true;
            }
            return false;
        };
        var originRequestConfiguration = productConfigure._requestItemConfiguration;
        productConfigure._requestItemConfiguration = function (listType, itemId)
        {
            if (listType == this.skuObject.listType) {
                itemId = this.skuObject.configurableItems[itemId];
            }
            return originRequestConfiguration.apply(this, [listType, itemId]);
        };

        // abstract admin sales instance
        function adminSalesInstance(addBySkuObject) {
            this.skuInstance = addBySkuObject;
            this.order = addBySkuObject.order;
            this.submitConfigured = function() {};
            this.updateErrorGrid = function(params) {};
            this.onSubmitSkuForm = function() {};
            var fields = $$(
                '#' + addBySkuObject.dataContainerId + ' input[name="sku"]',
                '#' + addBySkuObject.dataContainerId + ' input[name="qty"]'
            );
            for (var i = 0; i < fields.length; i++) {
                Event.observe(fields[i], 'keypress', addBySkuObject.formKeyPress.bind(addBySkuObject));
            }
        }

        // admin sales instance for 'Manage shopping cart'
        adminCheckout.prototype = new adminSalesInstance(this);
        adminCheckout.prototype.constructor = adminCheckout;
        function adminCheckout()
        {
            this.controllerRequestParameterNames = {customerId: 'customer', storeId: 'store'};
        }
        adminCheckout.prototype.submitConfigured = function()
        {
            // Save original source grids configuration to be restored later
            var oldSourceGrids = this.order.sourceGrids;
            // Leave only error grid (don't submit information from other grids right now)
            this.order.sourceGrids = {'sku_errors': this.skuInstance.errorSourceGrid};
            // Save old response handler function to override it
            var parentResponseHandler = this.order.loadAreaResponseHandler;
            this.order.loadAreaResponseHandler = function (response)
            {
                if (!response['errors']) {
                    // If response is empty loadAreaResponseHandler() won't update the area
                    response['errors'] = '<span></span>';
                }
                // call origin response handler function
                parentResponseHandler.call(this, response);
            };
            this.order.productGridAddSelected('sku');
            this.order.sourceGrids = oldSourceGrids;
        };
        adminCheckout.prototype.updateErrorGrid = function (params)
        {
            var oldLoadingAreas = this.order.loadingAreas;
            // We need to override this field, otherwise layout is going to be broken
            this.order.loadingAreas = 'errors';
            var url = this.order.loadBaseUrl + 'block/' + this.skuInstance.listType;
            if (!params['json']) {
                params['json'] = true;
            }
            new Ajax.Request(url, {
                parameters: this.order.prepareParams(params),
                loaderArea: 'html-body',
                onSuccess: function(transport)
                {
                    var response = transport.responseText.evalJSON();
                    if (!response.errors) {
                        // If response is empty loadAreaResponseHandler() won't update the area
                        response.errors = '<span></span>';
                    }
                    this.loadAreaResponseHandler(response);
                }.bind(this.order),
                onComplete: function ()
                {
                    this.loadingAreas = oldLoadingAreas;
                }.bind(this.order)
            })
        };

        // admin sales instance for order creation
        adminOrder.prototype = new adminSalesInstance(this);
        adminOrder.prototype.constructor = adminOrder;
        function adminOrder()
        {
            var skuAreaId = this.order.getAreaId('additional_area');

            this.controllerRequestParameterNames = {customerId: 'customerId', storeId: 'storeId'};
            this.order.itemsArea.skuButton = new ControlButton(Translator.translate('Add Products By SKU'));
            this.order.itemsArea.skuButton.onClick = function() {
                $(skuAreaId).show();
                var el = this;
                window.setTimeout(function () {
                    el.remove();
                }, 10);
            };
            this.order.itemsArea.onLoad = this.order.itemsArea.onLoad.wrap(function(proceed) {
                proceed();
                if (!$(skuAreaId).visible()) {
                    this.addControlButton(this.skuButton);
                }
            });
            this.order.dataArea.onLoad();
        }
        adminOrder.prototype.submitConfigured = function()
        {
            var area = ['errors', 'search', 'items', 'shipping_method', 'totals', 'giftmessage','billing_method'];
            var table = $('sku_errors_table');
            var elements = table.select('input[type=checkbox][name=sku_errors]:checked');
            var fieldsPrepare = {};
            fieldsPrepare['from_error_grid'] = '1';
            elements.each(function (elem) {
                if (!elem.value || (elem.value == 'on')) {
                    return;
                }
                var tr = elem.up('tr');
                if (tr) {
                    (function (fieldNames, parent, id) {
                        if (typeof fieldNames == 'string') {
                            fieldNames = [fieldNames];
                        }
                        for (var i = 0; i < fieldNames.length; i++) {
                            var elem = parent.select('input[name=' + fieldNames[i] + ']');
                            var paramKey = 'add_by_sku[' + id + '][' + fieldNames[i] + ']';
                            if (elem.length) {
                                fieldsPrepare[paramKey] = elem[0].value;
                            }
                        }
                    })(['qty', 'sku'], tr, elem.value)
                }
            });
            this.order.productConfigureSubmit('errors', area, fieldsPrepare, this.skuInstance.configuredSkus);
            this.skuInstance.configuredSkus = [];
        };
        adminOrder.prototype.updateErrorGrid = function(params)
        {
            this.order.loadArea('errors', true, params);
        };
        adminOrder.prototype.onSubmitSkuForm = function()
        {
            this.order.additionalAreaButton && Element.show(this.order.additionalAreaButton);
            this.order.itemsArea.addControlButton(this.order.itemsArea.skuButton);
        };

        // Strategy
        if (this.order instanceof (window.AdminOrder || Function)) {
            this._provider = new adminOrder();
        } else {
            this._provider = new adminCheckout();
        }
        this.controllerRequestParameterNames = this._provider.controllerRequestParameterNames;
    },

    removeFailedItem : function (obj)
    {
        try {
            var sku = obj.up('tr').select('td')[0].select('input[name="sku"]')[0].value;
            this._provider.updateErrorGrid({'remove_sku': sku});
        } catch (e) {
            return false;
        }
    },

    removeAllFailed : function ()
    {
        this._provider.updateErrorGrid({'sku_remove_failed': '1'});
    },

    submitConfigured : function ()
    {
        this._provider.submitConfigured();
    },

    /**
     * Delete element from queue
     *
     * @param obj Element to remove
     */
    del : function(obj)
    {
        var tr = obj.up('tr');
        if( $('id_' + tr.id) ) {
            var itemId = $('id_' + tr.id).value;
            var newElement = document.createElement('input');
            newElement.type = 'hidden';
            newElement.value = itemId;
            newElement.name = 'deleteSku[]';
            $(this.dataContainerId).appendChild(newElement);
        }
        tr.remove();
    },

    /**
     * Submit selected CSV file (if any)
     */
    submitSkuForm : function ()
    {
        this._provider.onSubmitSkuForm();

        // Hide 'add by SKU' area on order creation page (not available on manage shopping cart page)
        this.order.hideArea && this.order.hideArea('additional_area');
        var $form = new Element('form', {
            'action': this.fileUploadUrl,
            'method': 'post',
            'enctype': 'multipart/form-data'
        });

        $form.insert(new Element('input', {'type': 'hidden', 'name': this.fileUploadedParamName, 'value': '0'}));
        var $file = Element.select('body', 'input[name="' + this.fileFieldName + '"]')[0];
        if ($file.value) {
            // Inserting element to other place removes it from the old one. Creating new file input element on same place
            // to avoid confusing effect that it has disappeared.
            $file.up().insert(new Element('input', {'type': 'file', 'name': this.fileFieldName}));
            // We need to insert same file input element into the form. Simple copy of name/value doesn't work.
            $form.insert($file);
            $form[this.fileUploadedParamName].value = '1';
        }

        // sku form rows
        var requestParams = {};
        var sku = '';
        $('sku_table').select('input[type=text]').each(function (elem) {
            var qty = 0;
            if (elem.name == 'sku') {
                sku = elem.value;
            } else if (elem.name == 'qty') {
                qty = elem.value;
            } else {
                return;
            }
            if (sku != '') { // SKU field processed before qty, so if it is empty - nothing has been entered there
                var paramKey = 'add_by_sku[' + sku + '][qty]';
                requestParams[paramKey] = qty;
            }
        });
        if (!Object.keys(requestParams).length && !$file.value) {
            return false;
        }

        for (var i in requestParams) {
            $form.insert(new Element('input', {'type': 'hidden', 'name': i, 'value': requestParams[i]}));
        }

        // general fields
        $form.insert(new Element('input', {'type': 'hidden', 'name': this.controllerRequestParameterNames['customerId'], 'value': this.order.customerId}));
        $form.insert(new Element('input', {'type': 'hidden', 'name': this.controllerRequestParameterNames['storeId'], 'value': this.order.storeId}));
        $form.insert(new Element('input', {'type': 'hidden', 'name': 'form_key', 'value': FORM_KEY}));

        // For IE we must make the form part of the DOM, otherwise browser refuses to submit it
        Element.select(document, 'body')[0].insert($form);
        $form.submit();
        // Show loader
        varienLoaderHandler.handler.onCreate({options: {loaderArea: true}});
        return true;
    },

    /**
     * Configure a product
     *
     * @param id Product ID
     */
    configure : function (id, sku)
    {
        var descrElem = $('sku_' + sku);
        this.qtyElement = Element.select(descrElem.up('tr'), 'input[name="qty"]')[0];
        if (typeof(this.configurableItems[sku]) == 'undefined') {
            this.configurableItems[sku] = id;
        }

        // Don't process configured element by addBySku() observer method (it won't be serialized by serialize())
        productConfigure.setConfirmCallback(this.listType, function ()
        {
            // It is vital to push string element, check this line in configure.js:
            // this.itemsFilter[listType].indexOf(itemId) != -1
            productConfigure.skuObject.configuredSkus.push(sku);
            var $notice = Element.select(descrElem, '.notice');
            if ($notice.length) {
                // Remove message saying product requires configuration
                $notice[0].remove();
            }
            var $qty = productConfigure.getCurrentConfirmedQtyElement();
            if ($qty) { // Grouped products do not have this
                // Synchronize qtys between configure window and grid
                productConfigure.skuObject.qtyElement.value = $qty.value;
            }
        });
        productConfigure.showItemConfiguration(this.listType, sku);
        productConfigure.setShowWindowCallback(this.listType, function() {
            // sync qty of grid and qty of popup
            var qty = productConfigure.skuObject.qtyElement.value;
            if (qty && !isNaN(qty)) {
                var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                if (formCurrentQty) {
                    formCurrentQty.value = qty;
                }
            }
        })
    },

    /**
     * Intercept click on "Add to cart" button and submit sku instead of executing original action
     */
    observeAddToCart : function ()
    {
        this.addToCartButtonEvents = [];
        var that = this;
        $('products_search').select('button.button-to-cart').each(function (button)
        {
            // Save original event
            that.addToCartButtonEvents[button.id] = button.onclick;
            // Submit CSV file or perform an original event
            button.onclick = function ()
            {
                that.submitSkuForm() || that.addToCartButtonEvents[this.id]();
                that.clearAddForm();
            }
        });
    },

    /**
     * Return add form to untouched state
     */
    clearAddForm : function ()
    {
        var $rows = $(this.dataContainerId).select('tr');
        var rowNum = $rows.length;
        for (var i = 1; i < rowNum; i++) {
            // Remove all rows except the first
            $rows[i].remove();
        }
        // First row input fields: set empty SKU and qty
        $rows[0].select('input[name="sku"]')[0].value = '';
        $rows[0].select('input[name="qty"]')[0].value = '';
    },

    /**
     * Add parameters for error source grid (see adminCheckout.submitConfigured() described in constructor)
     *
     * @param params
     */
    addErrorSourceGrid : function (params)
    {
        this.errorSourceGrid = params;
    },

    formKeyPress : function (event)
    {
        if(event.keyCode==Event.KEY_RETURN){
            this.submitSkuForm();
        }
        return false;
    }
};
