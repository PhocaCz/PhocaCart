<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
class PhocacartRenderAdmin
{
	/*public static function quickIconButton( $link, $image, $text, $imgUrl ) {

		return '<div class="thumbnails ph-icon" style="text-align: center">'
		.'<a class="thumbnail ph-icon-inside" style="text-align: center" href="'.$link.'">'
		.HTMLHelper::_('image', $imgUrl . $image, $text )
		.'<br />'.$text.'</a></div>'. "\n";
	}
	*/
	public static function quickIconButton( $link, $text = '', $icon = '', $color = '') {

	    $s = PhocacartRenderStyle::getStyles();
		$o = '';
		$o .= '<div class="thumbnails ph-icon">';
		/*$o .= '   <div class="ph-icon-inside-box"><a class="thumbnail ph-icon-inside" href="'.$link.'"><span style="color: '.$color.';opacity: 0.6;" class="glyphicon glyphicon-'.$icon.' ph-icon-cp-large"></span></a></div>';
		$o .= '   <div class="ph-text-inside-box"><a class="ph-text-inside" href="'.$link.'"><span class="ph-icon-cp-title">'.$text.'</span></a></div>';
		*/
		$o .= '   <div class="ph-icon-inside-box"><a class=" icon thumbnail ph-icon-inside" href="'.$link.'" style="background-color: '.$color.'20;"><i style="color: '.$color.';" class="'.$icon.' ph-icon-cp-large"></i></a></div>';
		$o .= '   <div class="ph-text-inside-box"><a class="ph-text-inside" href="'.$link.'"><span class="ph-icon-cp-title">'.$text.'</span></a></div>';

		$o .= '</div>';


		return $o;
	}

	public static function getLinks() {
		$app	= Factory::getApplication();
		$option = $app->input->get('option');
		$oT		= strtoupper($option);

		$links =  array();
		switch ($option) {


			case 'com_phocacart':
				$links[]	= array('Phoca Cart site', 'https://www.phoca.cz/phocacart');
				$links[]	= array('Phoca Cart documentation site', 'https://www.phoca.cz/documentation/category/116-phoca-cart-component');
				$links[]	= array('Phoca Cart download site', 'https://www.phoca.cz/download/category/100-phoca-cart-component');
				$links[]	= array('Phoca Cart extensions', 'https://www.phoca.cz/phocacart-extensions');
			break;

		}

		$links[]	= array('Phoca News', 'https://www.phoca.cz/news');
		$links[]	= array('Phoca Forum', 'https://www.phoca.cz/forum');

		$components 	= array();
		$components[]	= array('Phoca Gallery','phocagallery', 'pg');
		$components[]	= array('Phoca Guestbook','phocaguestbook', 'pgb');
		$components[]	= array('Phoca Download','phocadownload', 'pd');
		$components[]	= array('Phoca Documentation','phocadocumentation', 'pdc');
		$components[]	= array('Phoca Favicon','phocafavicon', 'pfv');
		$components[]	= array('Phoca SEF','phocasef', 'psef');
		$components[]	= array('Phoca PDF','phocapdf', 'ppdf');
		$components[]	= array('Phoca Restaurant Menu','phocamenu', 'prm');
		$components[]	= array('Phoca Maps','phocamaps', 'pm');
		$components[]	= array('Phoca Font','phocafont', 'pf');
		$components[]	= array('Phoca Email','phocaemail', 'pe');
		$components[]	= array('Phoca Install','phocainstall', 'pi');
		$components[]	= array('Phoca Template','phocatemplate', 'pt');
		$components[]	= array('Phoca Panorama','phocapanorama', 'pp');
		$components[]	= array('Phoca Commander','phocacommander', 'pcm');
		$components[]	= array('Phoca Photo','phocaphoto', 'ph');
		$components[]	= array('Phoca Cart','phocacart', 'pc');

		$banners	= array();
		$banners[]	= array('Phoca Restaurant Menu','phocamenu', 'prm');
		$banners[]	= array('Phoca Cart','phocacart', 'pc');

		$o = '';
		$o .= '<p>&nbsp;</p>';
		$o .= '<h4 style="margin-bottom:5px;">'.Text::_($oT.'_USEFUL_LINKS'). '</h4>';
		$o .= '<ul>';
		foreach ($links as $k => $v) {
			$o .= '<li><a style="text-decoration:underline" href="'.$v[1].'" target="_blank">'.$v[0].'</a></li>';
		}
		$o .= '</ul>';

		$o .= '<div>';
		$o .= '<p>&nbsp;</p>';
		$o .= '<h4 style="margin-bottom:5px;">'.Text::_($oT.'_USEFUL_TIPS'). '</h4>';

		$m = mt_rand(0, 10);
		if ((int)$m > 0) {
			$o .= '<div>';
			$num = range(0,(count($components) - 1 ));
			shuffle($num);
			for ($i = 0; $i<3; $i++) {
				$numO = $num[$i];
				$o .= '<div style="float:left;width:33%;margin:0 auto;">';
				$o .= '<div><a style="text-decoration:underline;" href="https://www.phoca.cz/'.$components[$numO][1].'" target="_blank">'.HTMLHelper::_('image',  'media/'.$option.'/images/administrator/icon-box-'.$components[$numO][2].'.png', ''). '</a></div>';
				$o .= '<div style="margin-top:-10px;"><small><a style="text-decoration:underline;" href="https://www.phoca.cz/'.$components[$numO][1].'" target="_blank">'.$components[$numO][0].'</a></small></div>';
				$o .= '</div>';
			}
			$o .= '<div style="clear:both"></div>';
			$o .= '</div>';
		} else {
			$num = range(0,(count($banners) - 1 ));
			shuffle($num);
			$numO = $num[0];
			$o .= '<div><a href="https://www.phoca.cz/'.$banners[$numO][1].'" target="_blank">'.HTMLHelper::_('image',  'media/'.$option.'/images/administrator/b-'.$banners[$numO][2].'.png', ''). '</a></div>';

		}

		$o .= '<p>&nbsp;</p>';
		//$o .= '<h4 style="margin-bottom:5px;">'.Text::_($oT.'_PLEASE_READ'). '</h4>';
		//$o .= '<div><a style="text-decoration:underline" href="https://www.phoca.cz/phoca-needs-your-help/" target="_blank">'.Text::_($oT.'_PHOCA_NEEDS_YOUR_HELP'). '</a></div>';

		$o .= '</div>';
		return $o;
	}

}
