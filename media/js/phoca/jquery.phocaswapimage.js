/*
 * jQuery Phoca SwapImage
 * http://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */
 var phSwapImage = function() {

	var phO = {
		
		phDefaultA		: '',
		phDefaultImg	: '',
		phDefaultHref	: '',
		phDefaultSrc	: '',
		phAttributesBox	: '',
		phCustomHref	: 0,


		Init: 		function(imgBox, form, select, customHref) {
			
			phO.phDefaultA		= jQuery(imgBox +' a');
			phO.phDefaultImg	= jQuery(imgBox +' a img');
			phO.phDefaultHref	= phO.phDefaultA.attr('href');
			phO.phDefaultSrc	= phO.phDefaultImg.attr('src');
			phO.phAttributesBox	= jQuery(form +' '+ select);
			phO.phCustomHref	= customHref;
			
		},
		
		Display: 	function() {
			
			
			//jQuery(document).on('change', phO.phAttributesBox, function(){
			phO.phAttributesBox.on('change', function(){
			
				// Set image from current selectbox (selectbox which was changed)
				var phNewSrc		= jQuery(this).find(':selected').data('image-option');
				// Find selected image from all attributes select boxes 
				var phSelectedSrc 	= false;
				phO.phAttributesBox.each(function( index ) {
				  var phFoundSrc	= jQuery(this).find(':selected').data('image-option');
				  if(phFoundSrc) {
					  
					  phSelectedSrc = phFoundSrc;
				  }
				});

				var phNewHref 		= phNewSrc;
				var phSelectedHref 	= phSelectedSrc;
				var phDefaultHref	= phO.phDefaultSrc;
				
				if (phO.phCustomHref) {
					phNewHref 		= phO.phDefaultHref;
					phSelectedHref 	= phO.phDefaultHref;
					phDefaultHref	= phO.phDefaultHref;
				}
					
				if (phNewSrc) {
					// New image found - change to new image
					phO.phDefaultA.attr('href', phNewHref);
					phO.phDefaultImg.attr('src', phNewSrc);
				} else if (!phNewSrc && phSelectedSrc) {
					// New image not found but there is some selected image yet (e.g. selected previously in other select box)
					phO.phDefaultA.attr('href', phSelectedHref);
					phO.phDefaultImg.attr('src', phSelectedSrc);
				} else {
					// Return back to default image (no new image, no selected image by other select box)
					phO.phDefaultA.attr('href', phO.phDefaultHref);
					phO.phDefaultImg.attr('src', phO.phDefaultSrc);
				}
			});

		
		}
	}
	return phO;
}