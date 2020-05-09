/*
 * jQuery Phoca Attribute Required
 * https://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */
 
/* CHECKBOXES */ 
 
/* Check if attribute is required (non standard attribute: not select but image checkboxes)
 * Image checkboxes cannot be checked by HTML5
 * The check must be done manually per javascript
 * There are different functions for different views because views can be in conflict - itemquick loaded in category
 */

 

jQuery(document).ready(function(){
	jQuery(document).on('click', '.phjAddToCart.phjItem button[type="submit"]', function() {
		jQuery(this).closest("form").find(' .checkbox-group.required input:checkbox').each(function() {// 1
			var phAttributeGroup 		= jQuery(this).closest(".checkbox-group").attr('id');// 2
			var phAttributeGroupItems	= jQuery('.phjAddToCart.phjItem #' + phAttributeGroup + ' input:checkbox');// 3
			
			phAttributeGroupItems.prop('required', true);
			if(phAttributeGroupItems.is(":checked")){
				phAttributeGroupItems.prop('required', false);
			}
		})
	});
})

jQuery(document).ready(function(){
	jQuery(document).on('click', '.phjAddToCart.phjCategory button[type="submit"]', function() {
		jQuery(this).closest("form").find(' .checkbox-group.required input:checkbox').each(function() {// 1
			var phAttributeGroup 		= jQuery(this).closest(".checkbox-group").attr('id');// 2
			var phAttributeGroupItems	= jQuery('.phjAddToCart.phjCategory #' + phAttributeGroup + ' input:checkbox');// 3
			phAttributeGroupItems.prop('required', true);
			if(phAttributeGroupItems.is(":checked")){
				phAttributeGroupItems.prop('required', false);
			}
		})
	});
})

jQuery(document).ready(function(){
	jQuery(document).on('click', '.phjAddToCart.phjItems button[type="submit"]', function() {
		jQuery(this).closest("form").find(' .checkbox-group.required input:checkbox').each(function() {// 1
			var phAttributeGroup 		= jQuery(this).closest(".checkbox-group").attr('id');// 2
			var phAttributeGroupItems	= jQuery('.phjAddToCart.phjItems #' + phAttributeGroup + ' input:checkbox');// 3
			phAttributeGroupItems.prop('required', true);
			if(phAttributeGroupItems.is(":checked")){
				phAttributeGroupItems.prop('required', false);
			}
		})
	});
})

jQuery(document).ready(function(){
	jQuery(document).on('click', '.phjAddToCart.phjItemQuick button[type="submit"]', function() {
		jQuery(this).closest("form").find(' .checkbox-group.required input:checkbox').each(function() {// 1
			var phAttributeGroup 		= jQuery(this).closest(".checkbox-group").attr('id');// 2
			var phAttributeGroupItems	= jQuery('.phjAddToCart.phjItemQuick #' + phAttributeGroup + ' input:checkbox');// 3
			phAttributeGroupItems.prop('required', true);
			if(phAttributeGroupItems.is(":checked")){
				phAttributeGroupItems.prop('required', false);
			}
		})
	});
})

jQuery(document).ready(function(){
	jQuery(document).on('click', '.phjAddToCart.phjPos button[type="submit"]', function() {
		jQuery(this).closest("form").find(' .checkbox-group.required input:checkbox').each(function() {// 1
			var phAttributeGroup 		= jQuery(this).closest(".checkbox-group").attr('id');// 2
			var phAttributeGroupItems	= jQuery('.phjAddToCart.phjPos #' + phAttributeGroup + ' input:checkbox');// 3
			phAttributeGroupItems.prop('required', true);
			if(phAttributeGroupItems.is(":checked")){
				phAttributeGroupItems.prop('required', false);
			}
		})
	});
})





