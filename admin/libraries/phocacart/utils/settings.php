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
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


//=========
//TYPES
//=========
//---------
//LOG (#__phocacart_logs)
// 1 ... payment
//---------

//---------
// RESPONSES (Info view)
// components\com_phocacart\views\info\tmpl\default.php
// components\com_phocacart\controllers\response.php
// 1 ... ORDER/NO DOWNLOAD
// 2 ... ORDER/ DOWNLOAD
// 3 ... PAYMENT / NO DOWNLOAD
// 4 ... PAYMENT / DOWNLOAD
// 5 ... PAYMENT CANCELED


// STOCK
// stock_checking - set if stock is checked or not
// stock_checkout - if stock is checked (stock_checking = 1), set if user can order product which is not on stock
//                    1 ... stock checkout enabled - cannot order, 0... stock checkout disabled - can order
// stockvalid - when we make checkout, it can happen that some of the product is not on stock, so we store this info to variable stockvalid
//            - e.g. we check the stock, product A is on stock, product B not and user ordered both - stockvalid = 0



class PhocacartUtilsSettings
{
	public static function getManagerGroup($manager) {

		$group = array();
		switch ($manager) {

			// Pathe needs to be set too

			case 'categoryimage':
				$group['f'] = 4;//File
				$group['i'] = 1;//Image
				$group['t'] = 'image';//Text
				$group['c']	= '&amp;tmpl=component';
			break;

			case 'productimage':
				$group['f'] = 5;//File
				$group['i'] = 1;//Image
				$group['t'] = 'image';//Text
				$group['c']	= '&amp;tmpl=component';
			break;

			case 'submititem':
				$group['f'] = 7;//File
				$group['i'] = 1;//Image
				$group['t'] = 'image';//Text
				$group['c']	= '&amp;tmpl=component';
			break;

			case 'productfile':
				$group['f'] = 3;//File
				$group['i'] = 0;//Image
				$group['t'] = 'file';//Text
				$group['c']	= '&amp;tmpl=component';
			break;

			case 'attributefile':
				$group['f'] = 3;//File
				$group['i'] = 0;//Image
				$group['t'] = 'file';//Text
				$group['c']	= '&amp;tmpl=component';
			break;

			case 'publicfile':
				$group['f'] = 6;//File
				$group['i'] = 0;//Image
				$group['t'] = 'file';//Text
				$group['c']	= '&amp;tmpl=component';
			break;

			case 'attachmentfile':
				$group['f'] = 8;//File
				$group['i'] = 0;//Image
				$group['t'] = 'file';//Text
				$group['c']	= '&amp;tmpl=component';
			break;

			default:
				$group['f'] = 0;//File
				$group['i'] = 0;//Image
				$group['t'] = 'file';//Text
				$group['c']	= '&amp;tmpl=component';
			break;

		}
		return $group;
	}

	public static function getListFilterParams($includeId = 0) {

		$pC 						= PhocacartUtils::getComponentParameters();
		$manufacturer_alias			= $pC->get( 'manufacturer_alias', 'manufacturer');
		$manufacturer_alias			= $manufacturer_alias != '' ? trim(PhocacartText::filterValue($manufacturer_alias, 'alphanumeric'))  : 'manufacturer';
		$p[] = 'price_from';
		$p[] = 'price_to';
		$p[] = 'tag';
		$p[] = 'label';
		$p[] = $manufacturer_alias;
		$p[] = 'a';
		$p[] = 's';


		$parameters = PhocacartParameter::getAllParameters();
		if (!empty($parameters)) {
			foreach ($parameters as $k => $v) {
				$p[] = trim(PhocacartText::filterValue($v->alias, 'alphanumeric'));
			}
		}
		if ($includeId > 0) {
			$p[] = 'id';
		}
		return $p;
	}

	/*
	public static function getUnit($unit = 0, $type = 'size' ) {

		switch($type) {

			case 'volume':
				$a = array(
					1 => array('COM_PHOCACART_MILLILITER', 'ml'),
					2 => array('COM_PHOCACART_CENTILITER', 'cl'),
					3 => array('COM_PHOCACART_LITER', 'l'),
					4 => array('COM_PHOCACART_PINT', 'pt'),
					5 => array('COM_PHOCACART_QUART', 'qt'),
					6 => array('COM_PHOCACART_GALLON', 'gal'),
					7 => array('COM_PHOCACART_OUNCE', 'fl oz')
				);
			break;

			case 'weight':
				$a = array(
					1 => array('COM_PHOCACART_GRAM', 'g'),
					2 => array('COM_PHOCACART_KILOGRAM', 'kg'),
					3 => array('COM_PHOCACART_POUND', 'lb'),
					4 => array('COM_PHOCACART_OUNCE', 'oz')
				);
			break;

			case 'size':
			default:
				$a = array(
					1 => array('COM_PHOCACART_MILLIMETER', 'mm'),
					2 => array('COM_PHOCACART_CENTIMETER', 'cm'),
					3 => array('COM_PHOCACART_METER', 'm'),
					4 => array('COM_PHOCACART_INCH', 'in'),
					5 => array('COM_PHOCACART_FOOT', 'ft')
				);
			break;
		}

		if ($unit == 0) {
			return $a;
		} else {
			if (isset($a[$unit])) {
				return $a[$unit];
			} else {
				return '';
			}
		}
	}*/


	public static function getTaxCalculationType($type) {
		switch($type) {
			case 1:
				return Text::_('COM_PHOCACART_PERCENTAGE');
			break;

			case 0:
			default:
				return Text::_('COM_PHOCACART_FIXED_AMOUNT');
			break;
		}
	}

	public static function getDiscountCalculationTypeArray() {

		$calcTypeArray = array();
		$calcTypeArray[0] = Text::_('COM_PHOCACART_FIXED_AMOUNT');
		$calcTypeArray[1] = Text::_('COM_PHOCACART_PERCENTAGE');

		return $calcTypeArray;
	}

	public static function getExtenstionsArray() {

		$a = array();
		$a['components'] 	= Text::_('COM_PHOCACART_COMPONENTS');
		$a['modules'] 		= Text::_('COM_PHOCACART_MODULES');
		$a['plugins'] 		= Text::_('COM_PHOCACART_PLUGINS');
		$a['templates'] 	= Text::_('COM_PHOCACART_TEMPLATES');
		$a['languages'] 	= Text::_('COM_PHOCACART_LANGUAGES');

		return $a;
	}

	public static function getExtenstionsJSONLinks($type) {


		$dir = 'https://raw.githubusercontent.com/PhocaCz/PhocaCart/master/extensions/';

		$date = new DateTime();
		$suffix = '?time='.$date->getTimestamp();

		$a = array();
		$a['components'] 	= 'components/components.json' . $suffix;
		$a['modules'] 		= 'modules/modules.json' . $suffix;
		$a['plugins'] 		= 'plugins/plugins.json' . $suffix;
		$a['templates'] 	= 'templates/templates.json' . $suffix;
		$a['languages'] 	= 'languages/languages.json' . $suffix;
		$a['news'] 			= 'news/news.json' . $suffix;

		if (isset($a[$type])) {
			return $dir . $a[$type];
		}

		return false;
	}

	public static function getExtensionsJSONObtainTypeText($type) {


		switch($type){
			case 0:
				return JText::_('COM_PHOCACART_PAID');// Paid
			break;
			case 1:
				return JText::_('COM_PHOCACART_FREE');// Free Install
			break;
			case 2:
				return JText::_('COM_PHOCACART_FREE');// Free Download
			break;
			case 3:
				return JText::_('COM_PHOCACART_FREE_REGISTER');// Free Download but register
			break;
			case 4:
				return JText::_('COM_PHOCACART_PAID_SUBSCRIPTION');// Paid - Subscription
			break;
			default:
				return '';
			break;
		}
	}


	public static function getAdditionalHitsType($type) {
		switch($type) {
			case 2:
				return Text::_('COM_PHOCACART_SEARCH_TERM');
			break;

			case 1:
			default:
				return Text::_('COM_PHOCACART_PRODUCT_VIEW');
			break;
		}

	}




	public static function getDefaultAllowedMimeTypesDownload() {
		return '{hqx=application/mac-binhex40}{cpt=application/mac-compactpro}{csv=text/x-comma-separated-values}{bin=application/macbinary}{dms=application/octet-stream}{lha=application/octet-stream}{lzh=application/octet-stream}{exe=application/octet-stream}{class=application/octet-stream}{psd=application/x-photoshop}{so=application/octet-stream}{sea=application/octet-stream}{dll=application/octet-stream}{oda=application/oda}{pdf=application/pdf}{ai=application/postscript}{eps=application/postscript}{ps=application/postscript}{smi=application/smil}{smil=application/smil}{mif=application/vnd.mif}{xls=application/vnd.ms-excel}{ppt=application/powerpoint}{wbxml=application/wbxml}{wmlc=application/wmlc}{dcr=application/x-director}{dir=application/x-director}{dxr=application/x-director}{dvi=application/x-dvi}{gtar=application/x-gtar}{gz=application/x-gzip}{php=application/x-httpd-php}{php4=application/x-httpd-php}{php3=application/x-httpd-php}{phtml=application/x-httpd-php}{phps=application/x-httpd-php-source}{js=application/x-javascript}{swf=application/x-shockwave-flash}{sit=application/x-stuffit}{tar=application/x-tar}{tgz=application/x-tar}{xhtml=application/xhtml+xml}{xht=application/xhtml+xml}{zip=application/x-zip}{mid=audio/midi}{midi=audio/midi}{mpga=audio/mpeg}{mp2=audio/mpeg}{mp3=audio/mpeg}{aif=audio/x-aiff}{aiff=audio/x-aiff}{aifc=audio/x-aiff}{ram=audio/x-pn-realaudio}{rm=audio/x-pn-realaudio}{rpm=audio/x-pn-realaudio-plugin}{ra=audio/x-realaudio}{rv=video/vnd.rn-realvideo}{wav=audio/x-wav}{bmp=image/bmp}{gif=image/gif}{jpeg=image/jpeg}{jpg=image/jpeg}{jpe=image/jpeg}{png=image/png}{tiff=image/tiff}{tif=image/tiff}{css=text/css}{html=text/html}{htm=text/html}{shtml=text/html}{txt=text/plain}{text=text/plain}{log=text/plain}{rtx=text/richtext}{rtf=text/rtf}{xml=text/xml}{xsl=text/xml}{mpeg=video/mpeg}{mpg=video/mpeg}{mpe=video/mpeg}{qt=video/quicktime}{mov=video/quicktime}{avi=video/x-msvideo}{flv=video/x-flv}{movie=video/x-sgi-movie}{doc=application/msword}{xl=application/excel}{eml=message/rfc822}{pptx=application/vnd.openxmlformats-officedocument.presentationml.presentation}{xlsx=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet}{docx=application/vnd.openxmlformats-officedocument.wordprocessingml.document}{rar=application/x-rar-compressed}{odb=application/vnd.oasis.opendocument.database}{odc=application/vnd.oasis.opendocument.chart}{odf=application/vnd.oasis.opendocument.formula}{odg=application/vnd.oasis.opendocument.graphics}{odi=application/vnd.oasis.opendocument.image}{odm=application/vnd.oasis.opendocument.text-master}{odp=application/vnd.oasis.opendocument.presentation}{ods=application/vnd.oasis.opendocument.spreadsheet}{odt=application/vnd.oasis.opendocument.text}{sxc=application/vnd.sun.xml.calc}{sxd=application/vnd.sun.xml.draw}{sxg=application/vnd.sun.xml.writer.global}{sxi=application/vnd.sun.xml.impress}{sxm=application/vnd.sun.xml.math}{sxw=application/vnd.sun.xml.writer}';
	}

	public static function getDefaultAllowedMimeTypesUpload() {
		return '{pdf=application/pdf}{ppt=application/powerpoint}{gz=application/x-gzip}{tar=application/x-tar}{tgz=application/x-tar}{zip=application/x-zip}{bmp=image/bmp}{gif=image/gif}{jpeg=image/jpeg}{jpg=image/jpeg}{jpe=image/jpeg}{png=image/png}{tiff=image/tiff}{tif=image/tiff}{txt=text/plain}{mpeg=video/mpeg}{mpg=video/mpeg}{mpe=video/mpeg}{qt=video/quicktime}{mov=video/quicktime}{avi=video/x-msvideo}{flv=video/x-flv}{doc=application/msword}';
	}

	public static function getHTMLTagsUpload() {
		return array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');
	}

	public static function getHTMLTagsExternalSource() {

		return array('a', 'b', 'blockquote', 'br', 'caption', 'center', 'cite', 'code', 'dd', 'div', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'img', 'li', 'ol', 'p', 'pre', 'small', 'span', 'strike', 'strong', 'sub', 'sup', 'table', 'td', 'th', 'tr', 'ul');
	}

	public static function isFullGroupBy() {

		$pC							= PhocacartUtils::getComponentParameters();
		$sql_only_full_group_by		= $pC->get( 'sql_only_full_group_by', 0 );
		return (bool)$sql_only_full_group_by;
	}

	public static function getShopType() {

		$type = array();
		$type[0] = 0;// common
		if (PhocacartPos::isPos()) {
			// POS
			$type[1] = 2;
		} else {
			// ONLINE SHOP
			$type[1] = 1;
		}
		return $type;
	}

	public static function getShopTypes() {

		return array(
			0 => Text::_('COM_PHOCACART_ALL'),
			1 => Text::_('COM_PHOCACART_ONLINE_SHOP'),
			2 => Text::_('COM_PHOCACART_POS')
		);
	}

	public static function getLangQuery($column, $lang) {

		$db 				= Factory::getDbo();
		// Possible settings
		//$pC					= PhocacartUtils::getComponentParameters();
		//$filter_lang_type	= $pC->get( 'filter_lang_type', 2 );
		//$type1 				= ' ' . $db->quoteName($column) . ' = '.$db->quote($lang);
		$type2 				= ' ' . $db->quoteName($column) . ' IN ('.$db->quote($lang).','.$db->quote('*').')';

		/*if ($filter_lang_type == 1) {
			return $type1;
		} else {
			return $type2;
		}*/

		return $type2;
	}

	public static function getProductConditionValues($condition) {

		switch((int)$condition) {

			case 1: return 'refurbished';	break;
			case 2: return 'used'; 			break;
			case 0: default: return 'new';	break;
		}

	}


	public static function getOrderStatusClass($status) {


	    $status = str_replace('COM_PHOCACART_STATUS_', '', $status);


        switch ($status) {
            case 'CANCELED':
                $class = 'label label-warning badge bg-warning ph-order-status-canceled';
            break;

            case 'COMPLETED':
                $class = 'label label-success badge bg-success ph-order-status-completed';
            break;

            case 'CONFIRMED':
                $class = 'label label-success badge bg-success ph-order-status-confirmed';
            break;

            case 'PENDING':
                $class = 'label label-info badge bg-info label-primary ph-order-status-pending';
            break;

            case 'REFUNDED':
                $class = 'label label-important label-danger badge bg-danger ph-order-status-refunded';
            break;

            case 'SHIPPED':
                $class = 'label label-info badge bg-info ph-order-status-shipped';
            break;

            default:
                $class = 'label label-default ph-order-status-default';
            break;

        }
        return $class;
    }
}
?>
