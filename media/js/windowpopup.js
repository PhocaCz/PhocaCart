/*
 * jQuery Phoca WindowPopup
 * https://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */
function phWindowPopup(url, name, rW, rH) {
	var w 		= screen.width / rW;
	var h 		= screen.height / rH;
	var params 	= 'width='+w+', height='+h+', resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no';
	phWindow 	= window.open(url, name, params);

	if(window.focus) {
		phWindow.focus();
	}
   return false;
}
