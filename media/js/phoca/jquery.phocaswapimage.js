/*
 * jQuery Phoca SwapImage
 * https://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */

jQuery(document).ready(function() {

	jQuery(document).on('change', '.phjProductAttribute', function(){
		

		var phProductIdName	= jQuery(this).data('product-id-name');
		var phProductImg	= '.phjProductImage' + phProductIdName;
		var phProductSource	= '.phjProductSource' + phProductIdName;// Webp source
		var phProductHref	= '.phjProductHref' + phProductIdName;

		var phDefaultSrc 	= jQuery(phProductImg).data('image');// image includes data-image attribute
		var phDefaultHref 	= jQuery(phProductHref).data('href');// image includes data-image attribute

		var phNewSrc		= jQuery(this).find(':selected,:checked').data('image-option');// Set image from current selectbox (selectbox which was changed)

		var phSelectedSrc 	= false; // Find selected image from all attributes of all select boxes in the form



		/* jQuery(this).each(function( index ) {
			var phFoundSrc	= jQuery(this).find(':selected,:checked').data('image-option');
			if(phFoundSrc) {
				phSelectedSrc = phFoundSrc;
			}
		}); */


		jQuery(this).closest("form").find('.phjProductAttribute').each(function() {
			var phFoundSrc	= jQuery(this).find(':selected,:checked').data('image-option');
			if(phFoundSrc) {
				phSelectedSrc = phFoundSrc;
			}
		})

		var phNewHref		= phNewSrc;
		var phSelectedHref	= phSelectedSrc;


		if (phNewSrc) {
			// New image found - change to new image
			jQuery(phProductHref).attr('href', phNewHref);
			jQuery(phProductImg).attr('src', phNewSrc);
			jQuery(phProductSource).attr('srcset', phNewSrc);//webp
		} else if (!phNewSrc && phSelectedSrc) {
			// New image not found but there is some selected image yet (e.g. selected previously in other select box)
			jQuery(phProductHref).attr('href', phSelectedHref);
			jQuery(phProductImg).attr('src', phSelectedSrc);
			jQuery(phProductSource).attr('srcset', phSelectedSrc);//webp
		} else {
			// Return back to default image (no new image, no selected image by other select box)
			jQuery(phProductHref).attr('href', phDefaultHref);
			jQuery(phProductImg).attr('src', phDefaultSrc);
			jQuery(phProductSource).attr('srcset', phDefaultSrc);//webp
		}

	})

})
