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
  * CMS JS Function
  */

function previewAction(formId, formObj, url){
    var formElem = $(formId);
    var previewWindowName = 'cms-page-preview-' + $('page_page_id').value;

    formElem.writeAttribute('target', previewWindowName);
    formObj.submit(url);
    formElem.writeAttribute('target', '');
}

function publishAction(publishUrl){
    setLocation(publishUrl);
}

function saveAndPublishAction(formObj, saveUrl){
    formObj.submit(saveUrl + 'back/publish/');
}

function dataChanged() {
   $$('p.form-buttons button.publish').each(function(e){
      var isVisible = e.style.display != 'none' && !$(e).hasClassName('no-display');
      
      if(e.id == 'publish_button' && isVisible) {
          e.style.display = 'none';
      } else if(!isVisible && e.id == 'save_publish_button') {
          e.style.display = '';
          $(e).removeClassName('no-display');
      }
   })
}

varienGlobalEvents.attachEventHandler('tinymceChange', dataChanged);
