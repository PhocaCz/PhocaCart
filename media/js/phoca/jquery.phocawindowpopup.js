/*
 * jQuery Phoca WindowPopup
 * https://www.phoca.cz
 *
 * Copyright (C) 2016 Jan Pavelka www.phoca.cz
 *
 * Licensed under the MIT license
 */
function phWindowPopup(url, name, rW, rH) {
	let w 		= screen.width / rW;
	let h 		= screen.height / rH;
	let params 	= 'width='+w+', height='+h+', resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no';
	let phWindow = window.open(url, name, params);

	if(window.focus) {
		phWindow.focus();
	}
   return false;
}
