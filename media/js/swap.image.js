/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
jQuery(document).ready(function(){
	var phDefaultHref = jQuery('.ph-item-image-full-box a').attr('href');
	var phDefaultSrc = jQuery('.ph-item-image-full-box a img').attr('src');
	jQuery("#phItemPriceBoxForm select.ph-item-input-select-attributes").change(function(){
		var phNewImg = jQuery(this).find(':selected').data('image-option');
		//if (phNewImg == 'default') {
			//jQuery('.ph-item-image-full-box a').attr('href', phDefaultHref);
			//jQuery('.ph-item-image-full-box a img').attr('src', phDefaultSrc);
		//} else
		if (phNewImg) {
			jQuery('.ph-item-image-full-box a').attr('href', phNewImg);
			jQuery('.ph-item-image-full-box a img').attr('src', phNewImg);
		} else {
			//jQuery('.ph-item-image-full-box a').attr('href', phDefaultHref);
			//jQuery('.ph-item-image-full-box a img').attr('src', phDefaultSrc);
		}
	})
});

