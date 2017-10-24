jQuery.fn.oldChosen = jQuery.fn.chosen
jQuery.fn.chosen = function(options) {
  var select = jQuery(this)
    , is_creating_chosen = !!options

   var style = 'display:visible; position:absolute; clip:rect(0,0,0,0);';
 
	if (is_creating_chosen && select.css('position') === 'absolute' && select.attr('style') != style) {
		// if we are creating a chosen and the select already has the appropriate styles added
		// we remove those (so that the select hasn't got a crazy width), then create the chosen
		// then we re-add them later
		select.removeAttr('style');
	}

	var ret = select.oldChosen(options)
	// only act if the select has display: none, otherwise chosen is unsupported (iPhone, etc)
	if (is_creating_chosen && select.css('display') === 'none') {

		// https://github.com/harvesthq/chosen/issues/515#issuecomment-33214050
		// only do this if we are initializing chosen (no params, or object params) not calling a method
		select.attr('style', style);
		select.attr('tabindex', -1);
	}
	return ret
}