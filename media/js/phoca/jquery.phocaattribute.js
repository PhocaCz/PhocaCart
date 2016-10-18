/*
 * jQuery Phoca Attribute
 * http://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */
var phAttribute = function() {

	var phO = {
		
		
		phSelectNameId	: '', /* ID Select Box */
		phSelectNameIdT	: '', /* ID BOX for Color Buttons */
		phSelectNameIdB	: '', /* ID of BOX for Select Box */
		phSelectNameIdC	: '', /* ID of Select Box (Chosen) */
		phType			: '',
		phClass			: '',
		phClassId		: '',

		Init: 		function(id, type) {
			
			phO.phSelectNameId	= '#phItemAttribute' + id;
			phO.phSelectNameIdT	= '#phItemHiddenAttribute' + id;
			phO.phSelectNameIdB	= '#phItemBoxAttribute' + id;
			phO.phSelectNameIdC	= '#phItemAttribute' + id;
			phO.phType			= type;
			if (phO.phType == 3) {
				// Image
				phO.phClass			= 'phSelectBoxImage';
			} else {
				// Color
				phO.phClass			= 'phSelectBoxButton';
			}
			phO.phClassId		= phO.phSelectNameIdT +' .'+ phO.phClass;
			
		},
		
		Display: 	function() {

			var phSelectName 	= jQuery(phO.phSelectNameId).attr('name');
			var phHiddenEl 		= jQuery('<input type="hidden" name="'+ phSelectName +'">');
			phHiddenEl.val(jQuery(phO.phSelectNameId).val());
			phHiddenEl.insertAfter(jQuery(phO.phSelectNameId));

			
			// ON START DISPLAY OR HIDE
			// jQuery(phO.phSelectNameId).hide();
			// jQuery(phO.phSelectNameIdB).hide();
			// Cannot be hidden because of html5 required field and its message
			// Hide select box even its chosen alternative
			jQuery(phO.phSelectNameIdT).css( "display", "block");
			jQuery(phO.phSelectNameIdB).css( {"display": "visible", 'position': 'absolute', 'clip': 'rect(0,0,0,0)' });

			// ON START TRANSFORM
			jQuery(phO.phSelectNameId + ' option').each(function() {
				
				/* Do not display default value (empty value), can be set by clicking back from other value */
				if (jQuery(this).val() != '') {
					
					if(phO.phType == 3) {
						// Image
						var phSBtn = jQuery('<div class="'+ phO.phClass	+'" data-value="'+ jQuery(this).val() +'" title="'+ jQuery(this).text() +'"><img src="'+ jQuery(this).data('image') +'" alt="'+ jQuery(this).text() +'" /></div>');
					} else {
						// Color
						var phSBtn = jQuery('<div class="'+ phO.phClass +'" style="background-color:' + jQuery(this).data('color') +'" data-value="'+ jQuery(this).val() +'" title="'+ jQuery(this).text() +'">'+ '&nbsp;' +'</div>');
					}
			
				
					if(jQuery(this).is(':selected')) { 
						phSBtn.addClass('on');
					}

					jQuery(phO.phSelectNameIdT).append(phSBtn);
				
				}
				
			});

			// ON CLICK
			//jQuery(document).on('click', phO.phClassId, function(e) {
			jQuery(phO.phClassId).on('click', function(e) {
				
				e.preventDefault();// Bootstrap modal (close and open again duplicates events)
				
				var isActive = jQuery(this).hasClass('on');
				
				if (isActive) {
					jQuery(this).removeClass('on');
					jQuery('input[name="'+ phSelectName +'"]').val('');
					jQuery(phO.phSelectNameId).val('').change();// Because of required field
				} else {
					jQuery(phO.phClassId).removeClass('on');//Remove when multiple
					jQuery(this).addClass('on');
					jQuery('input[name="'+ phSelectName +'"]').val(jQuery(this).data('value'));
					jQuery(phO.phSelectNameId).val(jQuery(this).data('value')).change();// Because of required field
				}

			});
			
			
		}
	}
	return phO;
}