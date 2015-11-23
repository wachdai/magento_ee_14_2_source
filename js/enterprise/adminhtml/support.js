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

var SysreportPopupForm = {
    openDialog: function(url) {
        if ($('widget_window') && typeof(Windows) != 'undefined') {
            Windows.focus('widget_window');
            return;
        }
        this.dialogWindow = Dialog.info(null, {
            draggable: true,
            resizable: false,
            closable: true,
            className: 'magento',
            windowClassName: 'popup-window',
            title: Translator.translate('Generate system report'),
            top: 150,
            width: 750,
            height: 350,
            zIndex: 1000,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: 'widget_window',
            onClose: this.closeDialog.bind(this)
        });
        new Ajax.Updater('modal_dialog_message', url, {evalScripts: true});
    },
    closeDialog: function(window) {
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            // IE fix - hidden form select fields after closing dialog
            WindowUtilities._showSelect();
            window.close();
        }
    },
    submitForm: function(formId, redirectUrl) {
        if (!this.createForm) {
            this.createForm = new varienForm(formId, true);
        }
        if (this.createForm.validator.validate()) {
            var url = $(formId).action;

            var elements = [
                $('select_report_type')
            ].flatten();
            var serializedElements = Form.serializeElements(elements, true);

            disableElements('save');
            this.closeDialog();

            new Ajax.Updater(
                'modal_dialog_message',
                url,
                {
                    method: 'post',
                    asynchronous: true,
                    evalScripts: true,
                    onFailure: function() {
                        location.href = redirectUrl;
                    },
                    onComplete: function() {
                        location.href = redirectUrl;
                    },
                    parameters: serializedElements
                }
            );
        }
    }
};

function toggleGridFieldSet(element, containerId)
{
    var expanded = $(element).hasClassName('expanded');
    if (expanded) {
        $(containerId).hide();
        $(element).removeClassName('expanded');
    } else {
        $(containerId).show();
        $(element).addClassName('expanded');
    }
}

document.observe("dom:loaded", function() {
    $$('tr').each(function(item) {
        item.observe('click', function() {
            if (item.hasClassName('selected-row')) {
                item.removeClassName('selected-row');
            } else {
                item.addClassName('selected-row');
            }
        });
    });

    $$('span.cell-value-flag-yes').each(function(item) {
          item.up().addClassName('flag-yes');
    });

    $$('span.cell-value-flag-no').each(function(item) {
        item.up().addClassName('flag-no');
    });

    $$('span.cell-value-flag-processing').each(function(item) {
        item.up().addClassName('flag-processing');
    });

    $$('.sysreport-fieldset .fieldset').each(function(item) {
        item.hide();
    });
});
