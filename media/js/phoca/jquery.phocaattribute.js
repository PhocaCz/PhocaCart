/*
 * jQuery Phoca Attribute
 * http://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */

/* SELECT BOXES */
/* Changes Selects to color or image attributes*/

function phChangeAttributeType(typeView) {
	
	typeView = typeof typeView !== 'undefined' ? '.phj' + typeView : '';
	
	var phProductAttribute = typeView + '.phjProductAttribute';// Find all select boxes which should be transformed to color or image
	var phCleanAttribute = typeView + '.phjCleanAttribute';// Clean previously transformed select boxes in case of ajax reload
	
	jQuery(phCleanAttribute).remove();
	
	
	jQuery(phProductAttribute).each(function() {
	
		var phClass = '';
		var phClassId = '';
	
		var phSelectNameId	= '#phItemAttribute' + jQuery(this).data('attribute-id-name');
		var phSelectNameIdT	= '#phItemHiddenAttribute' + jQuery(this).data('attribute-id-name');
		var phSelectNameIdB	= '#phItemBoxAttribute' + jQuery(this).data('attribute-id-name');
		var phType			= jQuery(this).data('attribute-type');// Type of attribute
		var phTypeView		= jQuery(this).data('type-view');// Type of view, e.g. ItemQuickView is reloaded by ajax
	

		if (phType == 3) {
			phClass			= 'phSelectBoxImage';// Image
		} else if (phType == 2) {
			phClass			= 'phSelectBoxButton';// Color
		}
		
		// Transform only attributes which are select box image - 3 or select box color - 2
		if (phClass != '') {
			
			phClassId			= phSelectNameIdT + ' .' + phClass;
			var phSelectName 	= jQuery(phSelectNameId).attr('name');
			var phHiddenEl 		= jQuery('<input type="hidden" name="'+ phSelectName +'">');
			phHiddenEl.val(jQuery(phSelectNameId).val());
			phHiddenEl.insertAfter(jQuery(phSelectNameId));


			// ON START DISPLAY OR HIDE
			// jQuery(phSelectNameId).hide();
			// jQuery(phSelectNameIdB).hide();
			// Cannot be hidden because of html5 required field and its message
			// Hide select box even its chosen alternative
			jQuery(phSelectNameIdT).css( "display", "block");
			jQuery(phSelectNameIdB).css( {"display": "visible", 'position': 'absolute', 'clip': 'rect(0,0,0,0)' });
			jQuery(phSelectNameIdB).addClass('phj' + phTypeView + ' phjCleanAttribute');

			// ON START TRANSFORM
			jQuery(phSelectNameId + ' option').each(function() {
				
				/* Do not display default value (empty value), can be set by clicking back from other value */
				if (jQuery(this).val() != '') {
					
					if (phType == 3) {
						// Image
						var phSBtn = jQuery('<div class="'+ phClass	+'" data-value="'+ jQuery(this).val() +'" title="'+ jQuery(this).text() +'"><img src="'+ jQuery(this).data('image') +'" alt="'+ jQuery(this).text() +'" /></div>');
					} else if (phType == 2) {
						// Color
						var phSBtn = jQuery('<div class="'+ phClass +'" style="background-color:' + jQuery(this).data('color') +'" data-value="'+ jQuery(this).val() +'" title="'+ jQuery(this).text() +'">'+ '&nbsp;' +'</div>');
					}
			
				
					if(jQuery(this).is(':selected')) { 
						phSBtn.addClass('on');
					}

					jQuery(phSelectNameIdT).append(phSBtn);
				
				}
				
			});


			// Change on Click event
			jQuery(phClassId).on('click', function(e) {
		
				e.preventDefault();// Bootstrap modal (close and open again duplicates events)
			
				var isActive = jQuery(this).hasClass('on');
				
				if (isActive) {
					jQuery(this).removeClass('on');
					jQuery('input[name="'+ phSelectName +'"]').val('');
					jQuery(phSelectNameId).val('').change();// Because of required field
				} else {
					jQuery(phClassId).removeClass('on');//Remove when multiple
					jQuery(this).addClass('on');
					jQuery('input[name="'+ phSelectName +'"]').val(jQuery(this).data('value'));
					jQuery(phSelectNameId).val(jQuery(this).data('value')).change();// Because of required field
				}
			})
			
		}
	
	})
}
 
jQuery(document).ready(function() {
	phChangeAttributeType();
})
 
