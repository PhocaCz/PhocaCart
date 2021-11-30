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

namespace Phoca\Render;

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;
use Joomla\CMS\Layout\FileLayout;

class Adminview
{
	public $view 			= '';
	public $viewtype		= 2;
	public $option			= '';
	public $optionLang  	= '';
	public $compatible		= false;
	public $sidebar 		= true;
	protected $document		= false;

	public function __construct(){

		$app				= Factory::getApplication();
		$version 			= new Version();
		$this->compatible 	= $version->isCompatible('4.0.0-alpha');
		$this->view			= $app->input->get('view');
		$this->option		= $app->input->get('option');
		$this->optionLang = strtoupper($this->option);
		$this->sidebar 		= Factory::getApplication()->getTemplate(true)->params->get('menu', 1) ? true : false;
		$this->document	  = Factory::getDocument();


		//switch($this->view) {
         //   default:
				HTMLHelper::_('behavior.formvalidator');
				HTMLHelper::_('behavior.keepalive');
				HTMLHelper::_('jquery.framework', false);

				if (!$this->compatible) {
					HTMLHelper::_('behavior.tooltip');
					HTMLHelper::_('formbehavior.chosen', 'select');
				}
		//	break;
		//}

		HTMLHelper::_('stylesheet', 'media/'.$this->option.'/duotone/joomla-fonts.css', array('version' => 'auto'));
		HTMLHelper::_('stylesheet', 'media/'.$this->option.'/css/administrator/'.str_replace('com_', '', $this->option).'.css', array('version' => 'auto'));

		if ($this->compatible) {
			HTMLHelper::_('stylesheet', 'media/'.$this->option.'/css/administrator/4.css', array('version' => 'auto'));
		} else {
			HTMLHelper::_('stylesheet', 'media/'.$this->option.'/css/administrator/3.css', array('version' => 'auto'));
		}

	}

	public function startHeader() {

		$layoutSVG 	= new FileLayout('svg_definitions', null, array('component' => $this->option));
		return $layoutSVG->render(array());

	}

	public function startCp() {

		$o = array();
		if ($this->compatible) {

			if ($this->sidebar) {

			} else {
				$o[] = '<div class="row">';
				$o[] = '<div id="j-main-container" class="col-md-2">'. JHtmlSidebar::render().'</div>';
				$o[] = '<div id="j-main-container" class="col-md-10">';
			}

		} else {
			$o[] = '<div id="j-sidebar-container" class="span2">' . JHtmlSidebar::render() . '</div>'."\n";
			$o[] = '<div id="j-main-container" class="span10">'."\n";
		}

		return implode("\n", $o);
	}

	public function endCp() {

		$o = array();
		if ($this->compatible) {
			if ($this->sidebar) {

			} else {

				$o[] = '</div></div>';
			}
		} else {
			$o[] = '</div>';
		}

		return implode("\n", $o);
	}

	public function startForm($option, $view, $itemId, $id = 'adminForm', $name = 'adminForm', $class = '', $layout = 'edit',  $tmpl = '') {


		if ($layout != '') {
			$layout = '&layout='.$layout;
		}
		if ($view != '') {
			$viewP = '&view='.$view;
		}
		if ($tmpl != '') {
			$tmpl = '&tmpl='.$tmpl;
		}

		$containerClass = 'container';
		if ($this->compatible) {
			$containerClass = '';
		}

		return '<div id="'.$view.'"><form action="'.Route::_('index.php?option='.$option . $viewP . $layout . '&id='.(int) $itemId . $tmpl).'" method="post" name="'.$name.'" id="'.$id.'" class="form-validate '.$class.'" role="form">'."\n"
		.'<div id="phAdminEdit" class="'.$containerClass.'"><div class="row">'."\n";
	}

	public function endForm() {
		return '</div></div>'."\n".'</form>'."\n".'</div>'. "\n" . $this->ajaxTopHtml();
	}

	public function startFormRoute($view, $route, $id = 'adminForm', $name = 'adminForm') {
		return '<div id="'.$view.'"><form action="'.Route::_($route).'" method="post" name="'.$name.'" id="'.$id.'" class="form-validate">'."\n"
		.'<div id="phAdminEdit" class="row">'."\n";
	}

	public function ajaxTopHtml($text = '') {
		$o = '<div id="ph-ajaxtop">';
		if ($text != '') {
			$o .= '<div id="ph-ajaxtop-message"><div class="ph-loader-top"></div> '. strip_tags(addslashes($text)) . '</div>';
		}
		$o .= '</div>';
		return $o;
	}

	public function formInputs($task = '') {

		$o = '';
		$o .= '<input type="hidden" name="task" value="" />'. "\n";
		if ($task != '') {
			$o .= '<input type="hidden" name="taskgroup" value="'.strip_tags($task).'" />'. "\n";
		}
		$o .= HTMLHelper::_('form.token'). "\n";
		return $o;
	}

	public function groupHeader($form, $formArray , $image = '', $formArraySuffix = array(), $realSuffix = 0) {

		$md 	= 6;
		$columns = 12;
		$count = count($formArray);

		if ($image != '') {
			$mdImage = 2;
			$columns    = 10;
		}

		$md = round(($columns/(int)$count), 0);
		$md = $md == 0 ? 1 : $md;


		$o = '';

		$o .= '<div class="row title-alias form-vertical mb-3">';

		if (!empty($formArray)) {

			foreach ($formArray as $k => $v) {


				// Suffix below input
				if (isset($formArraySuffix[$k]) &&  $formArraySuffix[$k] != '' && $formArraySuffix[$k] != '<small>()</small>') {
					if ($realSuffix) {
						$value = $form->getInput($v) .' '. $formArraySuffix[$k];
					} else {
						$value = $formArraySuffix[$k];
					}
				} else {
					$value = $form->getInput($v);
				}


				$o .= '<div class="col-12 col-md-'.(int)$md.'">';

				$o .= '<div class="control-group">'."\n"
				. '<div class="control-label">'. $form->getLabel($v) . '</div>'."\n"
				. '<div class="clearfix"></div>'. "\n"
				. '<div>' . $value. '</div>'."\n"
				. '<div class="clearfix"></div>' . "\n"
				. '</div>'. "\n";

				$o .= '</div>';
			}
		}

		if ($image != '') {

			$o .= '<div class="col-12 col-md-'.(int)$mdImage.'">';
			$o .= '<div class="ph-admin-additional-box-img-box">'.$image.'</div>';
			$o .= '</div>';

		}


		$o .= '</div>';



		return $o;


	}

	public function group($form, $formArray, $clear = 0) {


		$o = '';
		if (!empty($formArray)) {
			if ($clear == 1) {
				foreach ($formArray as $value) {

					$description = Text::_($form->getFieldAttribute($value, 'description'));
					$descriptionOutput = '';
					if ($description != '') {
						$descriptionOutput = '<div role="tooltip">'.$description.'</div>';
					}

					$o .=

					//	'<div class="control-group">'."\n"
					 '<div class="control-label">'. $form->getLabel($value) . $descriptionOutput . '</div>'."\n"
					//. '<div class="clearfix"></div>'. "\n"
					. '<div>' . $form->getInput($value). '</div>'."\n"
					. '<div class="clearfix"></div>' . "\n";
					//. '</div>'. "\n";

				}
			} else {
				foreach ($formArray as $value) {

					$description = Text::_($form->getFieldAttribute($value, 'description'));
					$descriptionOutput = '';
					if ($description != '') {
						$descriptionOutput = '<div role="tooltip">'.$description.'</div>';
					}

					//$o .= $form->renderField($value) ;
					$o .= '<div class="control-group">'."\n"
					. '<div class="control-label">'. $form->getLabel($value)  . $descriptionOutput . '</div>'
					. '<div class="controls">' . $form->getInput($value). '</div>'."\n"
					. '</div>' . "\n";
				}
			}
		}
		return $o;
	}

	public function item($form, $item, $suffix = '', $realSuffix = 0) {
		$value = $o = '';
		if ($suffix != '' && $suffix != '<small>()</small>') {
			if ($realSuffix) {
				$value = $form->getInput($item) .' '. $suffix;
			} else {
				$value = $suffix;
			}
		} else {
			$value = $form->getInput($item);

		}


		$description = Text::_($form->getFieldAttribute($item, 'description'));
		$descriptionOutput = '';
		if ($description != '') {
			$descriptionOutput = '<div role="tooltip">'.$description.'</div>';
		}


		$o .= '<div class="control-group">'."\n";
		$o .= '<div class="control-label">'. $form->getLabel($item) . $descriptionOutput . '</div>'."\n"
		. '<div class="controls">' . $value.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}

	public function itemLabel($item, $label, $description = '') {


		$description = Text::_($description);
		$descriptionOutput = '';
		if ($description != '') {
			$descriptionOutput = '<div role="tooltip">'.$description.'</div>';
		}

		$o = '';
		$o .= '<div class="control-group">'."\n";
		$o .= '<div class="control-label">'. $label . $descriptionOutput . '</div>'."\n"
		. '<div class="controls">' . $item.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}

	public function itemText($item, $label, $class = '') {
		$o = '';
		$o .= '<div class="control-group ph-control-group-text">'."\n";
		$o .= '<div class="control-label">'. $label . '</div>'."\n"
		. '<div class="controls '.$class.'">' . $item.'</div>'."\n"
		. '</div>' . "\n";
		return $o;
	}






	public static function getCalendarDate($dateCustom) {

		$config = Factory::getConfig();
		$user 	= Factory::getUser();
		$filter = 'USER_UTC';//'SERVER_UTC'

		switch (strtoupper($filter)){
			case 'SERVER_UTC':
				if ($dateCustom && $dateCustom != Factory::getDbo()->getNullDate()) {
					$date = Factory::getDate($dateCustom, 'UTC');
					$date->setTimezone(new \DateTimeZone($config->get('offset')));
					$dateCustom = $date->format('Y-m-d H:i:s', true, false);
				}
			break;

			case 'USER_UTC':
				if ($dateCustom && $dateCustom != Factory::getDbo()->getNullDate()) {
					$date = Factory::getDate($dateCustom, 'UTC');
					$date->setTimezone(new \DateTimeZone($user->getParam('timezone', $config->get('offset'))));
					$dateCustom = $date->format('Y-m-d H:i:s', true, false);
				}
			break;
		}
		return $dateCustom;
	}

	/* CP */
	public function quickIconButton( $link, $text = '', $icon = '', $color = '', $item = '') {

		$o = '<div class="ph-cp-item '.$item.'-item-box">';
		$o .= ' <div class="ph-cp-item-icon">';
		$o .= '  <a class="ph-cp-item-icon-link" href="'.$link.'"><span style="background-color: '.$color.'20;"><i style="color: '.$color.';" class="phi '.$icon.' ph-cp-item-icon-link-large"></i></span></a>';
		$o .= ' </div>';

		$o .= ' <div class="ph-cp-item-title"><a class="ph-cp-item-title-link" href="'.$link.'"><span>'.$text.'</span></a></div>';
		$o .= '</div>';

		return $o;
	}


	public function getLinks($internalLinksOnly = 0) {


		$links =  array();
		switch ($this->option) {

			case 'com_phocacart':
				$links[]	= array('Phoca Cart site', 'https://www.phoca.cz/phocacart');
				$links[]	= array('Phoca Cart documentation site', 'https://www.phoca.cz/documentation/category/116-phoca-cart-component');
				$links[]	= array('Phoca Cart download site', 'https://www.phoca.cz/download/category/100-phoca-cart-component');
				$links[]	= array('Phoca Cart extensions', 'https://www.phoca.cz/phocacart-extensions');
			break;

			case 'com_phocamenu':
				$links[]	= array('Phoca Restaurant Menu site', 'https://www.phoca.cz/phocamenu');
				$links[]	= array('Phoca Restaurant Menu documentation site', 'https://www.phoca.cz/documentation/category/52-phoca-restaurant-menu-component');
				$links[]	= array('Phoca Restaurant Menu download site', 'https://www.phoca.cz/download/category/36-phoca-restaurant-menu-component');
			break;

			case 'com_phocagallery':
				$links[]	= array('Phoca Gallery site', 'https://www.phoca.cz/phocagallery');
				$links[]	= array('Phoca Gallery documentation site', 'https://www.phoca.cz/documentation/category/2-phoca-gallery-component');
				$links[]	= array('Phoca Gallery download site', 'https://www.phoca.cz/download/category/66-phoca-gallery');
			break;

		}

		$links[]	= array('Phoca News', 'https://www.phoca.cz/news');
		$links[]	= array('Phoca Forum', 'https://www.phoca.cz/forum');

		if ($internalLinksOnly == 1) {
		    return $links;
        }

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
		//$banners[]	= array('Phoca Cart','phocacart', 'pc');

		$o = '';
		$o .= '<p>&nbsp;</p>';
		$o .= '<h4 style="margin-bottom:5px;">'.Text::_($this->optionLang.'_USEFUL_LINKS'). '</h4>';
		$o .= '<ul>';
		foreach ($links as $k => $v) {
			$o .= '<li><a style="text-decoration:underline" href="'.$v[1].'" target="_blank">'.$v[0].'</a></li>';
		}
		$o .= '</ul>';

		$o .= '<div>';
		$o .= '<p>&nbsp;</p>';
		$o .= '<h4 style="margin-bottom:5px;">'.Text::_($this->optionLang.'_USEFUL_TIPS'). '</h4>';

		$m = mt_rand(0, 10);
		if ((int)$m > 0) {
			$o .= '<div>';
			$num = range(0,(count($components) - 1 ));
			shuffle($num);
			for ($i = 0; $i<3; $i++) {
				$numO = $num[$i];
				$o .= '<div style="float:left;width:33%;margin:0 auto;">';
				$o .= '<div><a style="text-decoration:underline;" href="https://www.phoca.cz/'.$components[$numO][1].'" target="_blank">'.HTMLHelper::_('image',  'media/'.$this->option.'/images/administrator/icon-box-'.$components[$numO][2].'.png', ''). '</a></div>';
				$o .= '<div style="margin-top:-10px;"><small><a style="text-decoration:underline;" href="https://www.phoca.cz/'.$components[$numO][1].'" target="_blank">'.$components[$numO][0].'</a></small></div>';
				$o .= '</div>';
			}
			$o .= '<div style="clear:both"></div>';
			$o .= '</div>';
		} else {
			$num = range(0,(count($banners) - 1 ));
			shuffle($num);
			$numO = $num[0];
			$o .= '<div><a href="https://www.phoca.cz/'.$banners[$numO][1].'" target="_blank">'.HTMLHelper::_('image',  'media/'.$this->option.'/images/administrator/b-'.$banners[$numO][2].'.png', ''). '</a></div>';

		}

		$o .= '<p>&nbsp;</p>';
		$o .= '<h4 style="margin-bottom:5px;">'.Text::_($this->optionLang.'_PLEASE_READ'). '</h4>';
		$o .= '<div><a style="text-decoration:underline" href="https://www.phoca.cz/phoca-needs-your-help/" target="_blank">'.Text::_($this->optionLang.'_PHOCA_NEEDS_YOUR_HELP'). '</a></div>';

		$o .= '</div>';
		return $o;
	}


	// TABS
	public function navigation($tabs, $activeTab = '') {

		if ($this->compatible) {
			return '';
		}

		$o = '<ul class="nav nav-tabs">';
		$i = 0;
		foreach($tabs as $k => $v) {
			$cA = 0;
			if ($activeTab != '') {
				if ($activeTab == $k) {
					$cA = 'class="active"';
				}
			} else {
				if ($i == 0) {
					$cA = 'class="active"';
				}
			}
			$o .= '<li '.$cA.'><a href="#'.$k.'" data-bs-toggle="tab">'. $v.'</a></li>'."\n";
			$i++;
		}
		$o .= '</ul>';
		return $o;
	}


	public function startTabs($active = 'general') {
		if ($this->compatible) {
			return HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => $active));
		} else {
			return '<div id="phAdminEditTabs" class="tab-content">'. "\n";
		}
	}

	public function endTabs() {
		if ($this->compatible) {
			return HTMLHelper::_('uitab.endTabSet');
		} else {
			return '</div>';
		}
	}

	public function startTab($id, $name, $active = '') {
		if ($this->compatible) {
			return HTMLHelper::_('uitab.addTab', 'myTab', $id, $name);
		} else {
			return '<div class="tab-pane '.$active.'" id="'.$id.'">'."\n";
		}
	}

	public function endTab() {
		if ($this->compatible) {
			return HTMLHelper::_('uitab.endTab');
		} else {
			return '</div>';
		}
	}

	public function itemCalc($id, $name, $value, $form = 'pform', $size = 1, $class = '') {

		switch ($size){
			case 3: $class = 'form-control input-xxlarge'. ' ' . $class;
			break;
			case 2: $class = 'form-control input-xlarge'. ' ' . $class;
			break;
			case 0: $class = 'form-control input-mini'. ' ' . $class;
			break;
			default: $class= 'form-control input-small'. ' ' . $class;
			break;
		}
		$o = '';
		$o .= '<input type="text" name="'.$form.'['.(int)$id.']['.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').']" id="'.$form.'_'.(int)$id.'_'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'" class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'" />';

		return $o;
	}

	public function itemCalcCheckbox($id, $name, $value, $form = 'pform' ) {

		$checked = '';
		if ($value == 1) {
			$checked = 'checked="checked"';
		}
		$o = '';
		$o .= '<input type="checkbox" name="'.$form.'['.(int)$id.']['.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').']" id="'.$form.'_'.(int)$id.'_'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'"  '.$checked.' />';

		return $o;
	}
}
?>
