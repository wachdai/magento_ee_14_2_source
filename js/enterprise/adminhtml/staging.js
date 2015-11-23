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
Enterprise.Staging = {};

Enterprise.Staging.Mapper = new Class.create();
Enterprise.Staging.Mapper.prototype = {
    templatePattern         : /(^|.|\r|\n)(\{\{(.*?)\}\})/,
    websiteRowTemplate      : null,
    storeRowTemplate        : null,
    storeAddBtnTemplate     : null,
    websiteIncrement        : 0,
    mapKeys                 : null,
    addWebsiteMapRow        : null,
    canMerge                : false,
    initialize : function(containerId, url, pageVar, sortVar, dirVar, filterVar, mergeForm, stores, websiteId)
    {
        this.formId = mergeForm;
        this.mergeForm = new varienForm(this.formId);
        this.containerId            = containerId;
        this.tableContainerId       = this.containerId + '_table';
        this.tableRowsContainerId   = this.containerId + '_rows';

        this.container              = $(this.containerId);
        this.tableContainer         = $(this.tableContainerId);
        this.tableRowsContainer     = $(this.tableRowsContainerId);

        this.stores = new $H(stores);

        this.grid = new varienGrid(containerId, url, pageVar, sortVar, dirVar, filterVar);

        this.mapKeys            = new $H();

        this.websiteRowTemplate     = new Template(
            this.getInnerElement('website_template').innerHTML,
            this.templatePattern
        );

        //removing template bc we don't need its inputs data in post
        this.getInnerElement('website_template').remove();

        this.storeRowTemplate = new Template(
            this.getInnerElement('store_template').innerHTML,
            this.templatePattern
        );

        //removing template bc we don't need its inputs data in post
        this.getInnerElement('store_template').remove();

        this.storeAddBtnTemplate = new Template(
            this.getInnerElement('store_add_btn_template').innerHTML,
            this.templatePattern
        );

        this.websiteIncrement = 0;
    },
    getInnerElement: function(elementName) {
        return $(this.containerId + '_' + elementName);
    },
    stagingWebsiteMapperRowInit : function(grid, row)
    {
        $(row).select('select').each(function(element) {
            element.parentRow = row;
            element.grid = grid;
            element.mapper = this;
            element.tableContainer = this.tableContainer;
            Event.observe(element, 'change', function (e) { selectWebsiteMap.call(this, e);});
        }.bind(this));
    },
    addWebsiteMap : function()
    {
        try {
            Element.insert(this.tableRowsContainer, {bottom: this.websiteRowTemplate.evaluate(this.getWebsiteVars())});
            var websiteRow = this.tableRowsContainer.lastChild;
            websiteRow.id = 'website-map-' + this.websiteIncrement;
            websiteRow.incrementId = this.websiteIncrement;

            websiteRow.stores = new $H();

            var fromElement           = $(websiteRow).select('.staging-mapper-website-from')[0];
            fromElement.prevValue       = fromElement.value;
            websiteRow.fromElement    = fromElement;
            websiteRow.toElement      = $(websiteRow).select('.staging-mapper-website-to')[0];

            this.stagingWebsiteMapperRowInit(this.grid, websiteRow);

            Element.insert(this.tableRowsContainer, {bottom: this.storeAddBtnTemplate.evaluate({})});
            var addStoreMapRow = this.tableRowsContainer.lastChild;
            addStoreMapRow.id = 'website-map-' + this.websiteIncrement + '-store-add-btn';
            addStoreMapRow.websiteIncrementId = this.websiteIncrement;
            addStoreMapRow.storeIncrementId = 0;
            var btn = $(addStoreMapRow).select('.staging-mapper-add-store-btn')[0];
            addStoreMapRow.btn = btn;

            addStoreMapRow.websiteRow = websiteRow;
            websiteRow.addStoreMapRow = addStoreMapRow;

            this.websiteIncrement++;
        } catch (Error) {
            console.log(Error);
        }

        return false;
    },
    getWebsiteVars : function()
    {
        return {};
    },
    enableBtn : function(btn)
    {
        btn.disabled = false;
        btn.removeClassName('disabled');
    },
    disableBtn : function(btn)
    {
        btn.disabled = true;
        btn.addClassName('disabled');
    },
    stagingStoreMapperRowInit : function(grid, row)
    {
        $(row).select('select').each(function(element) {
            element.parentRow = row;
            element.mapper = this;
            element.tableContainer = this.tableContainer;
            Event.observe(element, 'change', selectStoreMap);
        }.bind(this));
    },
    addStoreMap : function(btn)
    {
        try {
            var addNewRow = $(btn).up().up();

            Element.insert(addNewRow, {before: this.storeRowTemplate.evaluate(this.getStoreVars(addNewRow.websiteRow))});

            var storeRow = addNewRow.previousSibling;
            storeRow.id = 'website-map-' + addNewRow.websiteIncrementId + '-store-' + addNewRow.storeIncrementId;

            $(storeRow).addClassName('website-map-' + addNewRow.websiteIncrementId + '-store');
            storeRow.addNewRow = addNewRow;

            storeRow.select('.staging-mapper-store-from').each(
                    function(fromElement)
                    {
                        fromElement.websiteRow     = addNewRow.websiteRow;
                        fromElement.fromWebsite = addNewRow.websiteRow.fromWebsite;
                        storeRow.fromElement    = fromElement;
                    }
            );
            storeRow.select('.staging-mapper-store-to').each(
                    function(toElement)
                    {
                        toElement.websiteRow     = addNewRow.websiteRow;
                        toElement.toWebsite     = addNewRow.websiteRow.toWebsite;
                        storeRow.toElement      = toElement;
                    }
            );

            this.stagingStoreMapperRowInit(this.grid, storeRow);

            addNewRow.storeIncrementId++;

            this.disableBtn(addNewRow.btn);
        } catch (Error) {
            console.log(Error);
        }

        return false;
    },
    getStoreVars : function(websiteRow)
    {
        return {
            store_from_select  : this.getStoreFromSelect(websiteRow),
            store_to_select    : this.getStoreToSelect(websiteRow)
        };
    },
    removeStoreMap : function(btn)
    {
        try {
            var row = $(btn).up().up();
            var websiteRow     = row.fromElement.websiteRow;
            websiteRow.stores.unset(row.toStore);
            row.remove();

            this.enableBtn(row.addNewRow.btn);
        } catch (Error) {
            console.log(Error);
        }

        return false;
    },
    getStoreFromSelect : function(websiteRow)
    {
        var fromWebsite = websiteRow.fromWebsite;
        var toWebsite = websiteRow.toWebsite;
        var stores = this.stores.get(fromWebsite);
        if (typeof(stores) == 'undefined') {
            return '<span>No stores exists</span>' ;
        }
        var options = '<select name="map[stores]['+fromWebsite+'-'+toWebsite+'][from][]" class="validate-select-store  staging-mapper-store-from">';
        options+= '<option value=""></option>';
        stores.each(function(store){
            options+= '<option value="'+store.store_id+'">'+store.name+'</option>';
        });
        options+= '</option>';
        options = Object.toHTML(options);
        return options;
    },
    getStoreToSelect : function(websiteRow)
    {
        var fromWebsite = websiteRow.fromWebsite;
        var toWebsite = websiteRow.toWebsite;
        var stores = this.stores.get(toWebsite);
        if (typeof(stores) == 'undefined') {
            return '<span>No stores exists</span>' ;
        }
        var options = '<select name="map[stores]['+fromWebsite+'-'+toWebsite+'][to][]" class="validate-select-store staging-mapper-store-to">';
        options+= '<option value=""></option>';
        stores.each(function(store){
            options+= '<option value="'+store.store_id+'">'+store.name+'</option>';
        });
        options+= '</option>';
        options = Object.toHTML(options);
        options.toWebsite = toWebsite;
        return options;
    },
    stagingMergeConfig : function()
    {
        this.showScheduleConfigForm();
    },
    stagingMerge : function(additional_conditions)
    {
        if (!this.canMerge) {
            alert('Please, select websites to map');
            return;
        }

        if (additional_conditions && additional_conditions.merge_later == 1) {
            $('schedule_merge_later_flag').value = 1;
        } else {
            $('schedule_merge_later_flag').value = '';
        }

        if (!this.mergeForm.validator.validate()){
            return false;
        }

        this.mergeForm.submit();
    },
    showScheduleConfigForm : function()
    {
        if (!this.mapKeys.size()) {
            alert('Please, select websites to map');
            return;
        }

        if (!this.mergeForm.validator.validate()){
            return false;
        }

        var container = $('schedule_config_container');
        if (container && Prototype.Browser.IE) {
            var middle = parseInt(document.body.clientHeight/2)+document.body.scrollTop;
            container.style.position = 'absolute';
            container.style.top = middle;
        }

        container.style.visibility = '';
        container.show();
    },
    cancelMerge : function()
    {
        var container = $('schedule_config_container');

        $(this.formId).appendChild(container);
        container.hide();
    },
    stagingRollbackConfig : function()
    {
        alert('Rollback procession ... ... ');
    }
};

selectWebsiteMap = function(event)
{
    var element = Event.element(event);
    element = $(element);

    if (!element.parentRow || !element.mapper) {
       return;
    }

    var showStoreViewAddBtn = removeStore = false;
    var addStoreMapBtn   = element.parentRow.addStoreMapRow.btn;

    if (element.prevValue !== element.value) {
        removeStore = true;
        if (element.value) {
            showStoreViewAddBtn = true;
        }
    }

    var fromElement = element.parentRow.fromElement;
    if (fromElement) {
        element.parentRow.fromWebsite = fromElement.value;
        fromElement.prevValue   = fromElement.value;
    }

    var toElement     = element.parentRow.toElement;
    if (toElement) {
        element.parentRow.toWebsite = toElement.value;

        toElement.prevValue     = toElement.value;
    }

    if (fromElement && toElement) {
        if (fromElement.value && toElement.value) {
            element.mapper.canMerge = true;
            showStoreViewAddBtn     = true;
        } else {
            element.mapper.canMerge = false;
            removeStore             = true;
        }
    } else {
        element.mapper.canMerge = false;
    }

    if (removeStore) {
        // remove all stores related to website
        this.tableContainer.select('.'+element.parentRow.id+'-store').each(function(row){row.remove();});
        element.parentRow.stores = new $H();
    }

    if (showStoreViewAddBtn) {
        element.mapper.enableBtn(addStoreMapBtn);
    } else {
        element.mapper.disableBtn(addStoreMapBtn);
    }
};
selectStoreMap = function(event)
{
    var element = Event.element(event);
    element = $(element);

    var btn = element.parentRow.addNewRow.btn;

    if (!element.parentRow || !element.mapper) {
       return;
    }

    var fromElement = element.parentRow.fromElement;

    var toElement = element.parentRow.toElement;

    if (!fromElement || !toElement) {
        return;
    }

    var websiteRow = fromElement.websiteRow;

    if (fromElement == element) {
        processFromStoreElement(element, toElement);
    } else {
        processToStoreElement(fromElement, element);
    }

    if (fromElement.value && toElement.value) {
        element.mapper.enableBtn(btn);
        var currentMappedStores = websiteRow.stores.get(toElement.value);
        currentMappedStores.set(fromElement.value, true);
        //websiteRow.stores.set(toElement.value, currentMappedStores);
    } else {
        element.mapper.disableBtn(btn);
    }
};

processToStoreElement = function(fromElement, toElement)
{
    var btn = toElement.parentRow.addNewRow.btn;

    var websiteRow = fromElement.websiteRow;

    if (toElement.value) {
        var currentMappedStores = websiteRow.stores.get(toElement.value);
        if (!currentMappedStores) {
            currentMappedStores = new $H();
            websiteRow.stores.set(toElement.value, currentMappedStores);
        } else {
            if (toElement.prevValue) {
                toElement.value = toElement.prevValue;
            } else {
                toElement.value = '';
            }
            alert('Please, select an another store view to map.');
            return;
        }
    }

    if (toElement.prevValue) {
        websiteRow.stores.unset(toElement.prevValue);
    }

    toElement.parentRow.toStore = toElement.value;
    toElement.prevValue         = toElement.value;
}

processFromStoreElement = function(fromElement, toElement)
{
    var btn = fromElement.parentRow.addNewRow.btn;

    var websiteRow = fromElement.websiteRow;

    if (toElement.value) {
        var currentMappedStores = websiteRow.stores.get(toElement.value);
        currentMappedStores.set(fromElement.value, true);
        if (fromElement.prevValue) {
            currentMappedStores.unset(fromElement.prevValue);
            websiteRow.stores.set(toElement.value, currentMappedStores);
        }
    }
    fromElement.parentRow.fromStore = fromElement.value;
    fromElement.prevValue           = fromElement.value;
}

Enterprise.Staging.Form = new Class.create();
Enterprise.Staging.Form.prototype = {
    templatePattern         : /(^|.|\r|\n)(\{\{(.*?)\}\})/,
    containerId             : null,
    formId                  : null,
    itemTemplate            : null,
    proceedMessageTemplate  : null,
    successMessageTemplate  : null,
    items                   : null,
    proceedItems            : null,
    proceedIterator         : 0,
    countOfError            : 0,
    totalItems              : 0,
    fieldSuffix             : 0,
    initialize : function(containerId, formId, validationUrl, config, items, fieldSuffix)
    {
        this.containerId    = containerId;

        this.config         = config;

        this.formId         = formId;

        this.form           =  new varienForm(this.formId, validationUrl);

        this.items          = new $H(items);

        this.totalItems     = this.items.size();

        this.proceedItems   = new $H();

        this.fieldSuffix   = fieldSuffix;

        var itemTemplate = this.getInnerElement('item_template');
        if (itemTemplate) {
            this.itemTemplate   = new Template(
                this.getInnerElement('item_template').innerHTML,
                this.templatePattern
            );
        }

        var messageTemplate = this.getInnerElement('proceed_message_template');
        if (messageTemplate) {
            this.proceedMessageTemplate     = new Template(
                    messageTemplate.innerHTML,
                this.templatePattern
            );
        }
        var messageTemplate = this.getInnerElement('proceed_message_template');
        if (messageTemplate) {
            this.successMessageTemplate = new Template(
                messageTemplate.innerHTML,
                this.templatePattern
            );
        }
        this.createBtn = document.getElementsByClassName('create')[0];

        var visibilityElement = $('staging_website_visibility_' + this.fieldSuffix);
        if ($('staging_website_visibility_' + this.fieldSuffix)) {
            $('staging_website_visibility_' + this.fieldSuffix).observe(
                    'change', this.frontendAuthenticationCallback);
            this
                    .frontendAuthenticationCallback($('staging_website_visibility_' + this.fieldSuffix));
        }

    },

    addItem : function(key, item)
    {
        this.items.add(key, item);
    },

    runCreate : function()
    {
        this.submit();
    },

    getInnerElement: function(elementName)
    {
        return $(this.containerId + '_' + elementName);
    },

    setSettings : function(urlTemplate, websiteElement, setElement, typeElement)
    {
        var urlTemplate = new Template(urlTemplate, stagingTemplateSyntax);
        setLocation(urlTemplate.evaluate({websites: $F(websiteElement), set: $F(setElement), type: $F(typeElement)}));
    },

    saveAndContinueEdit : function(urlTemplate)
    {
        var urlTemplate = new Template(urlTemplate, this.templatePattern);
        var url = urlTemplate.evaluate({tab_id: this.activeTab.id});
        this.form.submit(url);
    },

    submit : function()
    {
        this.form.submit();
    },

    frontendAuthenticationCallback: function(event)
    {
        var element = null;
        try {
            element = Event.element(event);
        } catch (e) { }
        if (!element) {
            element = event;
        }
        parts = element.id.split('_');
        if (element.value == 'require_http_auth') {
            $('staging_website_master_login_' + parts[3]).addClassName('required-entry');
            $('staging_website_master_login_' + parts[3]).up('tr').show();
            $('staging_website_master_password_' + parts[3]).addClassName('required-entry');
            $('staging_website_master_password_' + parts[3]).up('tr').show();
        } else {
            $('staging_website_master_login_' + parts[3]).removeClassName('required-entry');
            $('staging_website_master_login_' + parts[3]).up('tr').hide();
            $('staging_website_master_password_' + parts[3]).removeClassName('required-entry');
            $('staging_website_master_password_' + parts[3]).up('tr').hide();
        }
    }
};

var stagingTemplateSyntax = /(^|.|\r|\n)({{(\w+)}})/;

function setSettings(urlTemplate, websiteElement, typeElement)
{
    var template = new Template(urlTemplate, stagingTemplateSyntax);
    setLocation(template.evaluate({master_website_id:$F(websiteElement),type:$F(typeElement)}));
}

function saveAndContinueEdit(urlTemplate)
{
    var template = new Template(urlTemplate, stagingTemplateSyntax);
    var url = template.evaluate({tab_id : enterprise_staging_tabsJsTabs.activeTab.id});
    stagingForm.submit(url);
}
