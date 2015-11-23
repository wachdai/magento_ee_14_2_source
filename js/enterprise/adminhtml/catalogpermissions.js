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

 if (!window.Enterprise) {
     window.Enterprise = {};
 }

 Enterprise.CatalogPermissions = {};

 Enterprise.CatalogPermissions.CategoryTab = Class.create();
 Object.extend(Enterprise.CatalogPermissions.CategoryTab.prototype, {
     templatesPattern: /(^|.|\r|\n)(\{\{(.*?)\}\})/,
     initialize: function (container, config) {
         this.container = $(container);
         this.config = config;
         this.permissions = $H((Object.isArray(this.config.permissions) ? {} : this.config.permissions));
         this.rowTemplate = new Template(this.config.row, this.templatesPattern);
         this.addButton = this.container.down('button.add');
         this.items = this.container.down('.items');
         this.onAddButton = this.handleAddButton.bindAsEventListener(this);
         this.onDeleteButton = this.handleDeleteButton.bindAsEventListener(this);
         this.onChangeWebsiteGroup = this.handleWebsiteGroupChange.bindAsEventListener(this);
         this.onFieldChange = this.handleUpdatePermission.bindAsEventListener(this);
         if (this.addButton) {
            this.addButton.observe('click', this.onAddButton);
         }
         this.index = 1;
         Validation.addAllThese([
            ['validate-duplicate-' + this.container.id, this.config.duplicate_message, function(v, elem) {
                return !$(elem).isDuplicate;
            }]
         ]);
         this.permissions.each(this.add.bind(this));
    },
    add: function () {
        var config = {
            index: this.index++
        };
        config.html_id = this.container.id + '_row_' + config.index;
        var params, i, l;

        var readOnly        = false;
        var isNewRow        = true;
        var limitWebsiteIds = null;
        if (this.config.limited_website_ids) {
            limitWebsiteIds = this.config.limited_website_ids;
        }

        if (arguments.length) {
            var isNewRow        = false;
            if (limitWebsiteIds) {
                if (!this.in_array(config.website_id, limitWebsiteIds)) {
                    readOnly = true;
                }
            }
            Object.extend(config, arguments[0].value);
            params = Object.keys(config);
            for (i=0, l=params.length; i < l; i ++) {
               if (params[i].match(/grant_/i)) {
                   // Workaround for IE
                   config[params[i] + '_' + config[params[i]]] = 'checked="checked"';
                   if (params[i] == 'grant_catalog_category_view'
                       && config[params[i]].toString() == '-2') {
                       config['grant_catalog_product_price'] = -2;
                       config['grant_catalog_product_price_disabled'] = 'disabled="disabled"';
                   }

                   if (params[i] == 'grant_catalog_product_price'
                       && config[params[i]].toString() == '-2') {
                       config['grant_checkout_items_disabled'] = 'disabled="disabled"';
                   }
               }
            }
            params.push('id');
            config.id = config.permission_id;
            config.permission_id = arguments[0].key;
        } else {
            config.permission_id = 'new_permission' + config.index;
            params = Object.keys(config);
            this.permissions.set(config.permission_id, {});
        }

        this.items.insert({top: this.rowTemplate.evaluate(config)});

        var row  = $(config.html_id);
        row.permissionId = config.permission_id;
        row.controller = this;

        for (i=0, l=params.length; i < l; i ++) {
            var field = row.down('.' + this.fieldClassName(params[i]));
            if (field) {
               if (!params[i].match(/grant_/i)) {
                   if (field.tagName.toUpperCase() != 'SELECT') {
                       field.value = config[params[i]];
                   } else {
                       for (var j=0, ln = field.options.length; j < ln; j++) {
                           if (config[params[i]] == null) {
                               config[params[i]] = '-1';
                           }
                           if (field.options[j].value == config[params[i]] &&
                               field.options[j].value.length == config[params[i]].length) {
                               field.value = field.options[j].value;
                           }
                       }
                  }
               }
            }
        }

        if (arguments.length == 0) {
             row.select('input[value="0"]').each(function(radio){
                 if (radio.type == 'radio') {
                     radio.checked = true;
                 }
             });
        }

        var fields = row.select('input', 'select', 'textarea');
        for (i = 0, l = fields.length; i < l; i ++) {
            fields[i].observe('change', this.onFieldChange);
            if (fields[i].type == 'radio') {
                fields[i].observe('click', this.onFieldChange);
            }
            if (fields[i].hasClassName('permission-duplicate')) {
                row.duplicateField = fields[i];
                row.duplicateField.isDuplicate = false;
                row.duplicateField.addClassName('validate-duplicate-' + this.container.id);
            }

            if (readOnly) {
                fields[i].disabled = true;
            }
        }

        if (websiteSelect = row.down('select.website-id-value')) {
            websiteSelect.observe('change', this.onChangeWebsiteGroup);
            if (isNewRow && limitWebsiteIds) {
                for (var optionId = 0; optionId < websiteSelect.options.length; optionId ++) {
                    var wsValue = websiteSelect.options[optionId].value;
                    if (wsValue != '' && !this.in_array(wsValue, limitWebsiteIds)) {
                        websiteSelect.remove(optionId);
                    }
                }
            }
        }
        if (groupSelect = row.down('select.customer-group-id-value')) {
            groupSelect.observe('change', this.onChangeWebsiteGroup);
        }

        var deleteButton = row.down('button.delete');
        if (deleteButton) {
            if (readOnly) {
                deleteButton.addClassName('disabled').disabled = true;
                row.addClassName('readonly');
            } else {
                deleteButton.observe('click', this.onDeleteButton);
            }
        }

        this.modifyParentValue(row);
    },

    handleAddButton: function (evt) {
        this.add();
        this.checkDuplicates();
        this.validate();
    },
    handleUpdatePermission: function (evt) {
        var field = $(Event.element(evt));
        var row = field.up('.permission-box');

        if (field.name && (field.type != 'radio' || field.checked)) {
            var fieldName = field.name.replace(/^(.*)\[([^\]]*)\]$/, '$2');
            this.permissions.get(row.permissionId)[fieldName] = field.value;

        }

        setTimeout(this.disableRadio.bind(this), 1);

        if (field.hasClassName('is-unique')) {
            this.checkDuplicates();
            this.validate();
        }
    },
    disableRadio: function ()
    {
        var rows = this.items.select('.permission-box');

        for (var i = 0, l = rows.length; i < l; i ++) {
            var row = rows[i];
            if (row.hasClassName('readonly')) {
                continue
            }
            if (row.down('.' + this.fieldClassName('grant_catalog_category_view') + '[value="-2"]').checked) {
                row.select('.' + this.fieldClassName('grant_catalog_product_price')).each(function(item){item.disabled = true;});
            } else {
                row.select('.' + this.fieldClassName('grant_catalog_product_price')).each(function(item){item.disabled = false;});
            }

            if (row.down('.' + this.fieldClassName('grant_catalog_category_view') + '[value="-2"]').checked
                || row.down('.' + this.fieldClassName('grant_catalog_product_price') + '[value="-2"]').checked) {
                row.select('.' + this.fieldClassName('grant_checkout_items')).each(function(item){item.disabled = true;});
            } else  {
                row.select('.' + this.fieldClassName('grant_checkout_items')).each(function(item){item.disabled = false;});
            }
        }
    },
    isDuplicate: function(row) {
        var needleString = this.rowUniqueKey(row);

        if (needleString.length == 0 || row.isDeleted) {
            return false;
        }

        var rows = this.items.select('.permission-box');
        for (var i = 0, l = rows.length; i < l; i ++) {
            if (!rows[i].isDuplicate &&
                !rows[i].isDeleted &&
                rows[i].permissionId != row.permissionId &&
                this.rowUniqueKey(rows[i]) == needleString) {
                return true;
            }
        }

        return false;
    },
    checkDuplicates: function () {
        var rows = this.items.select('.permission-box');
        for (var i = 0, l = rows.length; i < l; i ++) {
            rows[i].duplicateField.isDuplicate = this.isDuplicate(rows[i]);
        }
    },
    rowUniqueKey: function (row) {
        var fields = row.select('select.is-unique', 'input.is-unique');
        var key = '';
        for (var i=0, l=fields.length; i < l; i ++) {
            if (fields[i].value === '') {
               return '';
            }

            key += '_' + fields[i].value;

        }

        return key;
    },
    fieldClassName: function(fieldName) {
        return fieldName.replace(/_/g, '-') + '-value';
    },
    handleDeleteButton: function (evt) {
        var button = $(Event.element(evt));
        var row = button.up('.permission-box');
        row.isDeleted = true;
        row.down('.' + this.fieldClassName('_deleted')).value = '1';
        row.addClassName('no-display').addClassName('template');
        this.checkDuplicates();
        this.validate();
    },
    validate: function () {
        if (arguments.length > 0) {
            Validation.validate(arguments[0]);
            return;
        }
        var fields = this.container.select('input.permission-duplicate');
        for (var i=0, l=fields.length; i < l; i++) {
            Validation.validate(fields[i]);
        }
    },
    modifyParentValue : function(row) {
        var websiteId, groupId;
        if (websiteSelect = row.down('select.website-id-value')) {
            websiteId = websiteSelect.value;
        }
        else if (this.config['single_mode']) {
            websiteId = this.config['website_id'];
        }
        if (!websiteId) {
            websiteId = -1;
        }
        if (groupSelect = row.down('select.customer-group-id-value')) {
            groupId = groupSelect.value;
        }
        if (groupId == '') {
            groupId = -1;
        }
        var parentVals = this.config['parent_vals'][websiteId+'_'+groupId];
        if (typeof parentVals == 'undefined') {
            parentVals = {'category':0,'product':0,'checkout':0};
        }

        var grants = {
            'grant_catalog_category_view' : 'category',
            'grant_catalog_product_price' : 'product',
            'grant_checkout_items'        : 'checkout'
        };

        for(key in grants) {
            var val = parentVals[grants[key]];
            switch (val) {
            case '-1':
                var text = this.config['use_parent_allow'];
                break;
            case '-2':
                var text = this.config['use_parent_deny'];
                break;

            default:
                var text = this.config['use_parent_config'];
                break;
            }

            row.down('span.'+key).innerHTML = text;
        }
    },
    handleWebsiteGroupChange: function(e) {
        var row = $(Event.element(e)).up('.permission-box');
        this.modifyParentValue(row);
    },
    in_array: function  (needle, haystack) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
        return false;
    }
 })
