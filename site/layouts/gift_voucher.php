<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

 /*
 * +-------------------------------------------+
 * |        TYPE      |      FORMAT            |
 * +------------------+------------------------+
 * |                  |  html - HTML/SITE      |
 * |                  |  pdf - PDF             |
 * |                  |  mail - Mail           |
 * +------------------+------------------------+
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();
$d               = $displayData;

$color1 = '#A3464B';// Main
$color2 = '#272728';// Secondary

// START EXAMPLES CODE
$color1Eats = '#7A5E51';
$color1Moments = '#F39A3D';
$color1Student = '#745a75';

if ($d['gift_class_name'] == 'eats') {$color1 = $color1Eats;}
if ($d['gift_class_name'] == 'moments') {$color1 = $color1Moments;}
if ($d['gift_class_name'] == 'student') {$color1 = $color1Student;}
// END EXAMPLES CODE

$cs = array();
$cs['ph-gift-voucher-box'] 			= 'background: #ffffff; border: 3px dashed #252A34;, box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px; position: relative; padding: 0.5em;';
$cs['ph-gift-voucher-scissors'] 	= 'color: #000000; position: absolute; bottom: -0.80em; right: 1em; font-size: 1.5em;';
$cs['ph-gift-voucher-body'] 		= 'background: '.$color1.'; display:flex; width:100%;';
$cs['ph-gift-voucher-image-box'] 	= 'width: 100%; ';
$cs['ph-gift-voucher-image'] 		= 'object-fit: cover; width: 100%; height: 200px;';
$cs['ph-gift-voucher-title'] 		= ' color: #ffffff; text-shadow: 2px 4px 3px rgba(0,0,0,0.3); font-size: 3em; font-weight: bold; position: absolute; top: 1.5em; text-align: center; left: 0; right: 0;';
$cs['ph-gift-voucher-col1'] 		= 'color: #ffffff; width: 30%; text-align: center; padding-top: 5%; display: flex; align-items: stretch; justify-content: center;';
$cs['ph-gift-voucher-col2'] 		= 'color: #ffffff; width: 70%; text-align: left; font-size: 0.7em; padding: 5%;';
$cs['ph-gift-voucher-head'] 		= 'background: #ffffff; border-radius: 50%; width: 7em; height: 7em; margin: 1em; display: flex; flex-direction: column; align-items: center; justify-content: center;';
$cs['ph-gift-voucher-head-top'] 	= 'color: '.$color1.'; font-weight: bold; text-transform: uppercase; font-size: 1.8em; padding: 0;line-height:1;';
$cs['ph-gift-voucher-head-bottom'] 	= 'color: '.$color2.'; font-weight: bold; text-transform: uppercase; font-size: 1.1em;padding: 0;line-height:1;';
$cs['ph-gift-voucher-price'] 		= 'color: #ffffff; text-align: center; font-weight: bold; font-size: 2.6em;margin: 0.2em 0;';
$cs['ph-gift-voucher-code'] 		= 'color: '.$color2.';background-color: #ffffff; text-align: center; font-weight: bold; font-size: 2.6em;margin: 0.2em 0; padding:0.5em;';
$cs['ph-gift-voucher-from']	= '';
$cs['ph-gift-voucher-to']	= '';
$cs['ph-gift-voucher-date-to']	= '';
$cs['ph-gift-voucher-message']	= '';
$cs['ph-gift-voucher-description']	= '';

// SET EMPTY INLINE STYLES AS DEFAULT
$s = array();
foreach ($cs as $k => $v) {
	$s[$k] = '';
}
// SET CLASS NAMES
foreach($cs as $k => $v) {
	$c[$k] = $k;
}



// RENDER CSS IN STYLE TAG FOR HTML
if ($d['format'] == 'html') {
	echo '<style>';

	foreach($cs as $k => $v) {
		echo '.'.$k.' {'.$v.'}' . "\n";
	}

	// START EXAMPLES CODE
	// Dynamic change of design of gift voucher in HTML
	// EXAMPLES - Eats
	echo '
	.eats .ph-gift-voucher-body {background: '.$color1Eats.';}
	.eats .ph-gift-voucher-head-top {color: '.$color1Eats.';}';

	// EXAMPLES - moments
	echo '
	.moments .ph-gift-voucher-body {background: '.$color1Moments.';}
	.moments .ph-gift-voucher-head-top {color: '.$color1Moments.';}';

	// EXAMPLES - student
	echo '
	.student .ph-gift-voucher-body {background: '.$color1Student.';}
	.student .ph-gift-voucher-head-top {color: '.$color1Student.';}';
	// END EXAMPLES CODE

	echo '</style>';
}


// FIX image paths MAIL
$d['gift_description'] 	= str_replace('src="', 'src="'. JURI::root(), $d['gift_description']);
$d['gift_image'] 		= JURI::root() .  $d['gift_image'];

// ------------------------
// |     HTML | EMAIL     |
// ------------------------
if ($d['format'] == 'html' || $d['format'] == 'mail') {


	if ($d['format'] == 'mail') {




		// Specific case for mail
		// IMAGE - in html we use standard image because of chaning the image with help of javascript
		// MAIL - in mail we use background image because mail clients do not use negative margins
		$cs['ph-gift-voucher-title'] 		= ' color: #ffffff; text-shadow: 2px 4px 3px rgba(0,0,0,0.3); font-size: 4em; font-weight: bold; text-align: center; padding-top: 2.5em;padding-bottom:2.5em; background: url('.$d['gift_image'].'); background-repeat: no-repeat; background-size: cover;';
		$cs['ph-gift-voucher-head'] 		= 'background: #ffffff; border-radius: 50%; width: 10em; height: 10em; margin: auto;';
		$cs['ph-gift-voucher-head-top'] 	= 'color: '.$color1.'; font-weight: bold; text-transform: uppercase; font-size: 2em; text-align: center;padding-top:1em;';
		$cs['ph-gift-voucher-head-bottom'] 	= 'color: '.$color2.'; font-weight: bold; text-transform: uppercase; font-size: 1.3em; text-align: center;';

		// Inline styles for mail
		foreach ($cs as $k => $v) {
			$s[$k] = ' style="'.$v.'"';
		}

	}

	echo '<div'.$s['ph-gift-voucher-box'].' class="phAOGiftType '.$c['ph-gift-voucher-box'].' '.$d['gift_class_name'].'">';

	echo '<span'.$s['ph-gift-voucher-scissors'].' class="'.$c['ph-gift-voucher-scissors'].'">&#9986;</span>'. "\n";

	if ($d['gift_image'] != '' && $d['format'] == 'html') {
		echo '<div'.$s['ph-gift-voucher-image-box'].' class="'.$c['ph-gift-voucher-image-box'].'">';
		echo '<img'.$s['ph-gift-voucher-image'].' class="phAOGiftImage '.$c['ph-gift-voucher-image'].'" src="'.$d['gift_image'].'" alt="" />';
		echo '</div>';
	}

	if ($d['gift_title'] != '') {
		echo '<div'.$s['ph-gift-voucher-title'].' class="'.$c['ph-gift-voucher-title'].' phAOGiftTitle">' . $d['gift_title'].'</div>'. "\n";
	}

	echo '<div'.$s['ph-gift-voucher-body'].' class="'.$c['ph-gift-voucher-body'].'">'. "\n";

	echo '<div'.$s['ph-gift-voucher-col1'].' class="'.$c['ph-gift-voucher-col1'].'">'. "\n";

	echo '<div'.$s['ph-gift-voucher-head'].' class="'.$c['ph-gift-voucher-head'].'">'. "\n";
	echo '<div'.$s['ph-gift-voucher-head-top'].' class="'.$c['ph-gift-voucher-head-top'].'">'.JText::_('COM_PHOCACART_TXT_GIFT_VOUCHER_GIFT').'</div>';
	echo '<div'.$s['ph-gift-voucher-head-bottom'].' class="'.$c['ph-gift-voucher-head-bottom'].'">'.JText::_('COM_PHOCACART_TXT_GIFT_VOUCHER_VOUCHER').'</div>';
	echo '</div>';// end ph-gift-voucher-head

	echo '</div>'. "\n";// end ph-gift-voucher-col1

	echo '<div'.$s['ph-gift-voucher-col2'].' class="'.$c['ph-gift-voucher-col2'].'">'. "\n";

	if ($d['gift_description'] != '') {
		echo '<div'.$s['ph-gift-voucher-description'].' class="'.$c['ph-gift-voucher-description'].' phAOGiftDescription">'.$d['gift_description'].'</div>'. "\n";
	}

	echo '<div'.$s['ph-gift-voucher-price'].' id="phItemPriceGiftBox'. $d['typeview'] . (int)$d['product_id'].'" class="'.$c['ph-gift-voucher-price'].'">' . $d['discount'].'</div>'. "\n";

	if ($d['gift_sender_name'] != '') {
		echo '<div'.$s['ph-gift-voucher-from'].' class="'.$c['ph-gift-voucher-from'].'">'.JText::_('COM_PHOCACART_FROM').': <span class="phAOSenderName">'.$d['gift_sender_name'].'</span></div>'. "\n";
	}
	if ($d['gift_recipient_name'] != '') {
		echo '<div'.$s['ph-gift-voucher-to'].' class="'.$c['ph-gift-voucher-to'].'">'.JText::_('COM_PHOCACART_TO').':  <span class="phAORecipientName">'.$d['gift_recipient_name'].'</span></div>'. "\n";
	}
	if ($d['gift_sender_message'] != '') {
		echo '<div'.$s['ph-gift-voucher-message'].' class="'.$c['ph-gift-voucher-message'].' phAOSenderMessage">'.$d['gift_sender_message'].'</div>'. "\n";
	}

	if ($d['code'] != '') {
		echo '<div'.$s['ph-gift-voucher-code'].' class="'.$c['ph-gift-voucher-code'].' phAOGiftCode">'.$d['code'].'</div>'. "\n";
	}

	if ($d['valid_to'] != '') {
		echo '<div'.$s['ph-gift-voucher-date-to'].' class="'.$c['ph-gift-voucher-date-to'].'">'.JText::_('COM_PHOCACART_VALID_TILL').': <span class="phAOGiftDate">'.$d['valid_to'].'</span></div>'. "\n";
	}

	echo '</div>'. "\n";// end ph-gift-voucher-col2
	echo '</div>'. "\n"; // end ph-gift-voucher-body
	echo '</div>'. "\n"; // end ph-gift-voucher-box
}

// -----------------
// |     PDF      |
// -----------------
if ($d['format'] == 'pdf') {



	$cs['ph-gift-voucher-box'] 			= 'border: 1px dashed #252A34;';
	$cs['ph-gift-voucher-scissors'] 	= '';
	$cs['ph-gift-voucher-body'] 		= 'color: #fff;background-color: '.$color1.';';
	$cs['ph-gift-voucher-image-box'] 	= '';
	$cs['ph-gift-voucher-image'] 		= '';
	$cs['ph-gift-voucher-title'] 		= 'font-size: 18px;text-align:center;font-weight: bold; color: #ffffff;';
	$cs['ph-gift-voucher-col1'] 		= 'width: 30%;';
	$cs['ph-gift-voucher-col2'] 		= 'width: 70%;';
	$cs['ph-gift-voucher-head'] 		= 'text-align: center;';
	$cs['ph-gift-voucher-head-top'] 	= 'color: '.$color1.'; font-weight: bold; text-transform: uppercase; font-size: 13px;';
	$cs['ph-gift-voucher-head-bottom'] 	= 'color: '.$color2.'; font-weight: bold; text-transform: uppercase; font-size: 11px;';
	$cs['ph-gift-voucher-price'] 		= 'color: #ffffff; text-align: center; font-weight: bold; font-size: 14px;';
	$cs['ph-gift-voucher-code'] 		= 'color: '.$color2.';background-color: #ffffff; text-align: center; font-weight: bold; font-size: 14px;';

	if ($d['format'] == 'mail' || $d['format'] == 'pdf') {
		foreach ($cs as $k => $v) {
			$s[$k] = ' style="'.$v.'"';
		}
	}



	echo '<div nobr="true">';

	$svgScissors= '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="60" xmlns:v="https://vecta.io/nano"><path d="M98.031 14.192c-4.319-5.182-12.083-4.976-17.876-2.727L43.761 25.227c-10.613-5.766-21.078-4.075-21.086-6.898-.007-2.206 2.021-1.729 1.701-7.473-.307-5.515-6.078-9.579-11.519-9.201C7.411 1.639 1.78 5.828 1.748 11.582 1.36 17.379 6.25 22.748 12.016 23.11c6.757.986 18.705-3.141 24.345 6.897-4.158 7.724-11.574 7.767-18.281 7.401-5.568-.304-12.25 1.311-14.889 6.791-2.55 5.252-.012 12.709 5.884 14.297 5.952 2.164 14.109-.617 15.503-7.458 1.074-5.273-2.664-7.738-1.237-9.655 1.077-1.447 7.943-.631 20.155-6.159L82.99 49.015c4.989 1.377 11.081 1.312 15.482-3.602l-40.95-15.341 40.51-15.88zM16.784 6c5.753 3.19 5.309 11.89-.654 13.592-5.392 1.895-12.303-3.331-10.6-9.185.994-4.803 7.316-6.59 11.254-4.407zm.355 35.568c5.999 2.195 5.012 12.338-1.079 13.719-4.038 1.415-9.822-.587-10.245-5.347-.805-5.788 5.984-11.039 11.324-8.372z"/></svg>';

	$params = $d['pdf_instance']->serializeTCPDFtagParameters(array('@' . $svgScissors, $x='', $y='', $w='6', $h='4', $link='', $align='L', $palign='L', $border=0, $fitonpage=true));
	echo '<div style="text-align:center"><tcpdf style="text-align:center;" method="ImageSVG" params="'.$params.'" /></div>';


	echo '<table cellpadding="5"><tr><td'.$s['ph-gift-voucher-box'].' class="phAOGiftType '.$c['ph-gift-voucher-box'].' '.$d['gift_class_name'].'">';

	if ($d['gift_image'] != '') {
		echo '<div'.$s['ph-gift-voucher-image-box'].' class="'.$c['ph-gift-voucher-image-box'].'">';


		$params = $d['pdf_instance']->serializeTCPDFtagParameters(array($d['gift_image'], $x='', $y='', $w='', $h='', $type='', $link='', $align='', $resize=true, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox='CM', $hidden=false, $fitonpage=true, $alt=false, $altimgs=array()));
		echo '<div style="text-align:center"><tcpdf style="text-align:center;" method="Image" params="'.$params.'" /></div>';
		echo '</div>';
		echo '<tcpdf method="setPageMark" params="" />';


		$html = '<div'.$s['ph-gift-voucher-title'].' class="'.$c['ph-gift-voucher-title'].' phAOGiftTitle">' . $d['gift_title'].'</div>';
		$params = $d['pdf_instance']->serializeTCPDFtagParameters(array($w='', $h='', $x='', $y='', $html, $border=0, $ln=1, $fill=false, $reseth=true, $align='C', $autopadding=true));
		echo '<div style="font-size: 6px">&nbsp;</div>';
		echo '<div style="text-align:center"><tcpdf style="text-align:center;" method="writeHTMLCell" params="'.$params.'" /></div>';
		echo '<div style="font-size: 6px">&nbsp;</div>';


		echo '<table><tr'.$s['ph-gift-voucher-body'].' class="'.$c['ph-gift-voucher-body'].'">'. "\n";

		echo '<td'.$s['ph-gift-voucher-col1'].' class="'.$c['ph-gift-voucher-col1'].'">'. "\n";

		$svg = '<svg width="80" height="80"><circle cx="40" cy="40" r="35" fill="white" /></svg>';
		$params = $d['pdf_instance']->serializeTCPDFtagParameters(array('@' . $svg, $x='', $y='', $w='', $h='', $link='', $align='', $palign='', $border=0, $fitonpage=true));
		echo '<div style="text-align:center"><tcpdf style="text-align:center;" method="ImageSVG" params="'.$params.'" /></div>';
		echo '<div'.$s['ph-gift-voucher-head'].' class="'.$c['ph-gift-voucher-head'].'"><div style="font-size: 10px">&nbsp;</div>'. "\n";
		echo '<div'.$s['ph-gift-voucher-head-top'].' class="'.$c['ph-gift-voucher-head-top'].'">'.JText::_('COM_PHOCACART_TXT_GIFT_VOUCHER_GIFT').'</div>';
		echo '<div'.$s['ph-gift-voucher-head-bottom'].' class="'.$c['ph-gift-voucher-head-bottom'].'">'.JText::_('COM_PHOCACART_TXT_GIFT_VOUCHER_VOUCHER').'</div>';
		echo '</div>';// end ph-gift-voucher-head

		echo '</td>'. "\n";// end ph-gift-voucher-col1


		echo '<td'.$s['ph-gift-voucher-col2'].' class="'.$c['ph-gift-voucher-col2'].'">'. "\n";

		echo '<div>&nbsp;</div>';

		if ($d['gift_description'] != '') {
			$description = PhocacartText::removeFirstTag($d['gift_description']);
			echo '<div'.$s['ph-gift-voucher-description'].' class="'.$c['ph-gift-voucher-description'].' phAOGiftDescription">'.$description.'</div>'. "\n";
		}

		echo '<div'.$s['ph-gift-voucher-price'].' id="phItemPriceGiftBox'. $d['typeview'] . (int)$d['product_id'].'" class="'.$c['ph-gift-voucher-price'].'">' . $d['discount'].'</div>'. "\n";

		if ($d['gift_sender_name'] != '') {
			echo '<div'.$s['ph-gift-voucher-from'].' class="'.$c['ph-gift-voucher-from'].'">'.JText::_('COM_PHOCACART_FROM').': <span class="phAOSenderName">'.$d['gift_sender_name'].'</span></div>'. "\n";
		}
		if ($d['gift_recipient_name'] != '') {
			echo '<div'.$s['ph-gift-voucher-to'].' class="'.$c['ph-gift-voucher-to'].'">'.JText::_('COM_PHOCACART_TO').':  <span class="phAORecipientName">'.$d['gift_recipient_name'].'</span></div>'. "\n";
		}
		if ($d['gift_sender_message'] != '') {
			echo '<div'.$s['ph-gift-voucher-message'].' class="'.$c['ph-gift-voucher-message'].' phAOSenderMessage">'.$d['gift_sender_message'].'</div>'. "\n";
		}

		if ($d['code'] != '') {
			echo '<div'.$s['ph-gift-voucher-code'].' class="'.$c['ph-gift-voucher-code'].' phAOGiftCode">'.$d['code'].'</div>'. "\n";
		}

		if ($d['valid_to'] != '') {
			echo '<div'.$s['ph-gift-voucher-date-to'].' class="'.$c['ph-gift-voucher-date-to'].'">'.JText::_('COM_PHOCACART_VALID_TILL').': <span class="phAOGiftDate">'.$d['valid_to'].'</span></div>'. "\n";
		}
		echo '<div>&nbsp;</div>';

		echo '</td>'. "\n";// end ph-gift-voucher-col2


		echo '</tr></table>';// end ph-gift-voucher-body
	}

	echo '</td></tr></table>'. "\n"; // end ph-gift-voucher-box

	$params = $d['pdf_instance']->serializeTCPDFtagParameters(array('@' . $svgScissors, $x='', $y='', $w='6', $h='4', $link='', $align='R', $palign='R', $border=0, $fitonpage=true));
	echo '<div style="text-align:right"><tcpdf style="text-align:right;" method="ImageSVG" params="'.$params.'" /></div>';

	echo '</div>';// end no br

}
?>
