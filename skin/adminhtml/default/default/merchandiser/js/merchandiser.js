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
jQuery.noConflict();
jQuery(document).ready(function() {
    var merJSObject = new merchandiserJS();
    merJSObject.initFunction();
});


var merchandiserJS = Class.create();

merchandiserJS.prototype = {
    initialize : function(){
        //this.initFunction();
    },
    initFunction : function(){
        this.observerMultiSelect();
        this.setSortable();
        this.observeCategoryItemsButtons();
        this.observeCategoryMovetopButtons();
        this.observeMoreImageThumbnails();
        this.preventMoreImageLink();
        this.updateInputPositions();
    },
    observeCategoryItemsButtons : function(){
        jQuery('.remove-button, .cancel-remove-button').unbind('click').click( function() {
            var merJSObject = new merchandiserJS();
            jQuery(this).parents('.dragbox').remove();
            var liID = jQuery(this).parents('.dragbox').attr('id');
            var removedIds = $('removed_product_ids').value;
            if(removedIds.search(liID) == -1){
                $('removed_product_ids').value += liID;
            }
            merJSObject.updateInputPositions();
        });
        jQuery('.move-to-bottom-button').unbind('click').click( function() {
            jQuery('#loader_div').show();
            var elementId = jQuery(this).attr('id');
            var prodMoveBottButtonId = '';
            var selectedProducts = '';
            var isInnerSelected = 0;
            jQuery("li.p-selected").each(function() {
                prodMoveBottButtonId = jQuery(this).find('.move-to-bottom-button').attr('id');
                selectedProducts += prodMoveBottButtonId.replace('move-bottom-buttom-' , '') + ',';
                if(prodMoveBottButtonId == elementId){
                    isInnerSelected = 1;
                }
            });
            if(isInnerSelected == 0){
                selectedProducts = elementId.replace('move-bottom-buttom-' , '') + ',';
            }
            jQuery('#mer_prods').val(selectedProducts);
            jQuery('#move_bottom_form').submit();
        });
    },
    observeCategoryMovetopButtons : function() {
        jQuery('.move-to-top-button').unbind('click').click( function() {
            var merJSObject = new merchandiserJS();
            jQuery(this).parents('.dragbox').prependTo('#infinite_scroll');
            merJSObject.updateInputPositions();
        });
    },
    observeCategoryAdd : function() {
        var escapedId = jQuery("<div></div>").text(this.id).html();
        jQuery('.category-add-button').unbind('click').click( function() {
            var liHTML = '<li id="s' + escapedId + '" class="' + $('search-s' + this.id).className + '">';
            liHTML += $('search-s' + this.id).innerHTML;
            liHTML += '</li>';
            jQuery(liHTML).prependTo('#infinite_scroll');
            var merJSObject = new merchandiserJS();
            merJSObject.initFunction();
            merJSObject.hideDuplicates();
            merJSObject.updateInputPositions();
        });
    },
    hideDuplicates : function() {
        var item, duplItem, prodId, mandatory ='product';
        jQuery('.search-results .dragbox').each( function(index) {
            item = jQuery(this);
            var merJSObject = new merchandiserJS();
            prodId = merJSObject.getProdIdFromClass(item.attr('class'), mandatory)
            if (prodId) {
                duplItem = jQuery('#merchandiser-categories input.productid'+prodId);
                if (0 < duplItem.length) {
                    //item.find('.dragbox-content').remove(); 
                    item.find('.dragbox-content').hide(); 
                    item.find('.cannot-add').show();
                    item.find('.move-top').show();
                    jQuery('.move-top').unbind('click').click( function() {
                        var merJSObject = new merchandiserJS();
                        jQuery('#dragbox-sk' + this.id).show(); 
                        jQuery('#sk' + this.id).remove();
                        var removedIds = $('removed_product_ids').value;
                        if(removedIds.search('sk' + this.id) >= 0){
                            removedIds = removedIds.replace('sk' + this.id , '');
                            $('removed_product_ids').value = removedIds;
                        }
                        var liHTML = '<li id="sk' + this.id + '" class="' + $('search-sk' + this.id).className + '">';
                        liHTML += $('search-sk' + this.id).innerHTML;
                        liHTML += '</li>';
                        jQuery(liHTML).prependTo('#infinite_scroll');
                        
                        jQuery('#dragbox-sk' + this.id).hide(); 
                        merJSObject.observeCategoryItemsButtons();
                        merJSObject.observeCategoryMovetopButtons();
                        merJSObject.updateInputPositions();
                    });
                }
            }
        });
    },  
    setSortable : function() {
        jQuery('.dragbox').each(function() {
            jQuery(this).hover(function() {
                jQuery(this).find('h2').addClass('collapse');
            }, function() {
                jQuery(this).find('h2').removeClass('collapse');
            }).find('h2').click(function() {
                // Save state on change of collapse state of panel
                var merJSObject = new merchandiserJS();
                merJSObject.updateWidgetData();
            }).end().find('.configure').css('visibility', 'hidden');
        });
        jQuery('#merchandiser-categories .column').unbind('sortable').sortable( {
            connectWith : '.column',
            cancel : 'a',
            cursor : 'move',
            placeholder : 'placeholder',
            forcePlaceholderSize : true,
            opacity : 0.4,
            start : function(event, ui) {
                var merJSObject = new merchandiserJS();
                merJSObject.toggleDragboxEmpty(1);
            },
            stop : function(event, ui) {
                var merJSObject = new merchandiserJS();
                ui.item.css( {
                    'top' : '0',
                    'left' : '0'
                });
                
                merJSObject.toggleDragboxEmpty(0);
                
                if(jQuery('.p-selected').size() > 0){
                    var itemdraged = jQuery('#merchandiser-categories .column .dragbox').not('#dragbox-empty').eq(ui.item.index());
                    if (itemdraged.hasClass('p-selected')) {
                        itemdraged.removeClass('p-selected');
                        jQuery('.p-selected').remove().insertAfter(itemdraged);
                        itemdraged.addClass('p-selected');
                    };
                }
                merJSObject.initFunction();
            }
        }).disableSelection();
    },
    updateWidgetData : function() {
        var items = [];
        jQuery('.column').each(function() {
            var columnId = jQuery(this).attr('id');
            jQuery('.dragbox', this).each(function(i) {
                var collapsed = 0;
                if (jQuery(this).find('.dragbox-content').css('display') == "none")
                    collapsed = 1;
                // Create Item object for current panel
                var item = {
                    id : jQuery(this).attr('id'),
                    collapsed : collapsed,
                    order : i,
                    column : columnId
                };
                // Push item object into items array
                items.push(item);
            });
        });
        // Assign items array to sortorder JSON variable
        var sortorder = {
            items : items
        };
    },
    toggleDragboxEmpty : function(enable){
        var elLast = jQuery('#dragbox-empty');
        if (enable) {
            elLast.show();
        } else {
            elLast.detach();
            elLast.insertAfter(jQuery('#merchandiser-categories .column li:last-child'));
            elLast.hide();
        }
    },
    updateInputPositions : function(){
        var position = 0, match, oInput;
        jQuery('#infinite_scroll input.productid').not('.not-visible').each(function(){
            position++;
            jQuery(this).val(position);
            jQuery(this).parents('li').find('.dragbox-position').text(position);
        });

        if(position == 0){ // this code aaded to show/hide No Product Div
            jQuery('#no-product-message').show();
            this.unObserverScrollbar();
        }else{
            jQuery('#no-product-message').hide();
            observerScrollBar();
        }
    },
    removeDuplicatesAfterAjax : function() {
        this.removeDups();
    },
    getProdIdFromClass : function(classAttr, mandatory) {
        var ret = false;
        if ('string' == typeof(classAttr)) {
            if (!mandatory) {
                mandatory = 'product';
            }
            var re = new RegExp(mandatory+'(\\d+)');
            var match = classAttr.match(re);
            if (match && 1 < match.length) {
                ret = match[1];
            }
        }
        return ret;
    },
    preventMoreImageLink : function(){
        jQuery('.product-image-more-item a').unbind('click').click(function (e) {
            e.preventDefault();
        });
    },
    observeMoreImageThumbnails : function(){
        jQuery('div.product-image-more-item a').unbind('mouseenter mouseleave').bind('mouseenter mouseleave', function(event){
            var url = jQuery(this).attr('href');
            var mainImg = jQuery(this).parents('.cols2-left').find('img').first();
            var mainUrl = jQuery(this).parents('.cols2-left').find('a').first().attr('href');
            if(event.type == 'mouseenter') mainImg.attr('src', url);
            else mainImg.attr('src', mainUrl);
        })
    },
    showDimWindow : function(){
        jQuery('#dimwindow').show();
        jQuery('#dimwindow-inner').show();
    },
    hideDimWindow : function(){
        jQuery('#dimwindow').hide();
        jQuery('#dimwindow-inner').hide();
    },
    observerMultiSelect : function(){
        options = {selectedClass: 'p-selected'};
        var listdragbox = jQuery('#merchandiser-categories .column .dragbox').not('#dragbox-empty');
        listdragbox.unbind('click');
        listdragbox.click(function(e) {
            var parent = jQuery(this).parent();
            var myIndex = jQuery(parent.children()).index(jQuery(this));
            var prevIndex = jQuery(parent.children()).index(jQuery('.multiselectable-previous', parent));

            if (!e.ctrlKey && !e.shiftKey) { jQuery('.' + options.selectedClass, parent).removeClass(options.selectedClass); }
            jQuery(this).toggleClass(options.selectedClass);
            jQuery('.multiselectable-previous', parent).removeClass('multiselectable-previous');
            jQuery(this).addClass('multiselectable-previous');
            return true;
        }).disableSelection();
    },
    removeMassProducts : function(){
        jQuery('#massproductresult').text('');
        jQuery('#massproductresult').hide();
        jQuery("#progressbar").hide();
        var skus = jQuery('#massproduct_skus').val();
        skus = jQuery.trim(skus);
        if (skus == "") {
            return;
        }
        var sku_array = jQuery.trim(jQuery('#massproduct_skus').val()).split("\n");
        var sku_error = new Array();
        var sku_removed = new Array();

        sku_array.each(function(value){
            value = value.trim().replace(/ /g, "_");
            if (jQuery('#merchandiser-categories #sku-'+value).length) {
                jQuery('#merchandiser-categories #sku-'+value).remove();
                sku_removed.push(value);
            } else {
                sku_error.push(value);
            }
        });
        if( this.countElements(sku_error) ) {
            jQuery('#massproductresult').append('Following SKUs were not found: ');
            jQuery('#massproductresult').append(sku_error.join(', '));
            jQuery('#massproductresult').append('<br/>');
            jQuery('#massproductresult').show();
        }
        if( this.countElements(sku_removed) ) {
            jQuery('#massproductresult').append('Following SKUs were removed: ');
            jQuery('#massproductresult').append(sku_removed.join(', '));
            jQuery('#massproductresult').append('<br/>');
            jQuery('#massproductresult').show();
        }
        this.updateInputPositions();
        observerAddMassProducts();
        this.observerRemoveMassProducts();
    },
    openMassProductAssignment : function(){
        jQuery('#massproduct_skus').width(jQuery('#infinite_scroll').width() - 70);
        jQuery('#massproductassignent').width(jQuery('#infinite_scroll').width() - 50);
        jQuery('#massproductassignent').css('left' , (jQuery(window).width() - (jQuery('#infinite_scroll').width() - 40))/2);
        isRunning = true;
        jQuery('#massproductassignent').toggle();
        jQuery('#massproductassignent').animate({opacity:'1'},500); 
        jQuery('#background_tilt').width(jQuery(window).width());
        jQuery('#background_tilt').height(jQuery(window).height());
        jQuery('#background_tilt').animate({opacity:'0.5'},500); 
        jQuery('#background_tilt').toggle();
    },
    countElements : function(sku_array){
        var maxindex = 0;
            for(index in sku_array){
                if (sku_array.hasOwnProperty(index)) maxindex++;
            }
        return maxindex;
    },
    addMassProducts : function(asid,productinfourl){
        var skus = jQuery('#massproduct_skus').val();
        if (skus == "") return;
        
        jQuery('#massproductresult').text('');
        jQuery('#massproductresult').hide();
        jQuery("#progressbar").show();

        var sku_array = jQuery.trim(jQuery('#massproduct_skus').val()).split("\n");
        var maxindex = this.countElements(sku_array);

        var step_value = (100/maxindex);
        var sku_error = new Array();
        var sku_added = new Array();
        var sku_existed = new Array();

        var addmassfunction = function(sku_array,index){
            var productSku = sku_array[index];
            var checkSKU = "";
            if (typeof productSku != 'undefined') {
                sku_array[index] = productSku.trim();
                checkSKU = sku_array[index].replace(/ /g,"_");
            }
            var merJSObject = new merchandiserJS();
            if (jQuery('#merchandiser-categories #sku-'+checkSKU).length) {
                if (typeof productSku != 'undefined') {
                    sku_existed.push(sku_array[index]);
                }
                addmassfunction(sku_array,index-1);
            } else {
                var request = jQuery.ajax({
                  url: ''+productinfourl,
                  type: "GET",
                  data: {
                            asid : asid,
                            sku: sku_array[index]
                        },
                  dataType: "html"
                });
                 
                request.done(function(msg) {
                    if (msg.length) {
                      jQuery("#merchandiser-categories #infinite_scroll").prepend( msg );
                    //  jQuery('#no=product-message').hide();
                      if(typeof productSku != 'undefined'){
                        sku_added.push(sku_array[index]);
                      }
                      merJSObject.initFunction();
                    } else {
                        if(typeof productSku != 'undefined'){
                            sku_error.push(sku_array[index]);
                        }
                    };
                    index = index - 1;
                    jQuery("#progressbar").progressbar({
                      value: (step_value*(maxindex- index))
                    });
                    if (index >= 0) {
                        addmassfunction(sku_array,index);
                    } else {
                        if(merJSObject.countElements(sku_error) ) {
                            jQuery('#massproductresult').append('Following SKUs were not found: ');
                            jQuery('#massproductresult').append(sku_error.join(", "));
                            jQuery('#massproductresult').append('<br/>');
                            jQuery('#massproductresult').show();
                        }
                        if(merJSObject.countElements(sku_added) ) {
                            jQuery('#massproductresult').append('Following SKUs were added: ');
                            jQuery('#massproductresult').append(sku_added.join(", "));
                            jQuery('#massproductresult').append('<br/>');
                            jQuery('#massproductresult').show();
                        }
                        if(merJSObject.countElements(sku_existed) ) {
                            jQuery('#massproductresult').append('Following SKUs already exist in this category: ');
                            jQuery('#massproductresult').append(sku_existed.join(", "));
                            jQuery('#massproductresult').append('<br/>');
                            jQuery('#massproductresult').show();
                        }
                        jQuery("#progressbar").hide();
                        observerAddMassProducts();
                        merJSObject.observerRemoveMassProducts();
                        
                    }
                });
            }
        };
        addmassfunction(sku_array,maxindex);
    },
    hideMassProducts : function(){
        isRunning = false;
        jQuery('#massproductassignent').animate({opacity:'0'},500 , function(){
            jQuery('#massproductassignent').hide();
        }); 
        jQuery('#background_tilt').animate({opacity:'0'},500 , function(){
            jQuery('#background_tilt').hide();
        }); 
    },
    removeDups : function(){
        $$('li.productid').each(function (e){
            $$('input[type=hidden].productid.hiddenbox').each(function (ee) {
                if (e.id == ee.id) {
                    ee.remove();
                    return;
                }
            });
            var removedIds = $('removed_product_ids').value;
            if (removedIds.search(e.id) >= 0) {
                e.remove();
            }
        });
    },
    removeResults : function(){
        $('search-results').innerHTML = '';
    },
    observerRemoveMassProducts : function(){
        jQuery('#removeMassButton').unbind('click').click(function(){
            var merJSObject = new merchandiserJS();
            merJSObject.removeMassProducts();
        });    
    },
    unObserverScrollbar : function(){
        jQuery(window).unbind('scroll');
    }
}

