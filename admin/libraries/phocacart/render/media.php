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

class PhocacartRenderMedia
{
	public $jquery			= 0;
	protected $document		= false;
	protected $p			= array();
	protected $format		= '';
	protected $view			= '';


	public function __construct() {

		$app	= JFactory::getApplication();
		$params = $app->getParams();
		$this->p['load_bootstrap']			= $params->get( 'load_bootstrap', 1 );
		$this->p['load_chosen']				= $params->get( 'load_chosen', 1 );
        $this->p['load_main_css']			= $params->get( 'load_main_css', 1 );
		$this->p['equal_height']			= $params->get( 'equal_height', 1 );
		$this->p['fade_in_action_icons']	= $params->get( 'fade_in_action_icons', 0 );
		$this->p['dynamic_change_image']	= $params->get( 'dynamic_change_image', 0);
		$this->p['quantity_input_spinner']	= $params->get( 'quantity_input_spinner', 0);
		$this->p['icon_type']				= $params->get( 'icon_type', 'bs');

		$this->p['lazy_load_category_items']= $params->get( 'lazy_load_category_items', 0 );
		$this->p['lazy_load_categories']	= $params->get( 'lazy_load_categories', 0 );

		$this->format 	= $app->input->get('format', '', 'string');
		$this->view 	= $app->input->get('view', '', 'string');



		JHtml::_('jquery.framework', false);
		$this->document	= JFactory::getDocument();

        if ($this->p['load_main_css'] == 1) {
            JHtml::stylesheet('media/com_phocacart/css/main.css' );
        }


		if (PhocacartUtils::isView('pos')) {
			JHtml::stylesheet('media/com_phocacart/css/pos.css' );
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/ui/jquery-ui.min.js');
			JHtml::stylesheet('media/com_phocacart/js/ui/jquery-ui.min.css' );
			JHtml::stylesheet('media/com_phocacart/js/ui/phoca-ui.css' );
		}


	}

	public function loadLazyLoad() {

		$s = array();
		$s[] = '<script>';
		$s[] = ' window.lazyLoadOptions = {	';
		$s[] = '   elements_selector: ".ph-lazyload",';
		$s[] = '   load_delay: 0,';
		$s[] = ' };';

		$s[] = ' window.addEventListener(\'LazyLoad::Initialized\', function (event) {';
		$s[] = '   window.phLazyLoadInstance = event.detail.instance;';
		$s[] = ' }, false);';
		$s[] = '</script>';
		$s[] = '<script async src="'.JURI::root(true).'/media/com_phocacart/js/lazyload/lazyload.min.js"></script>';

		if ($this->p['lazy_load_category_items'] == 1 && ($this->view == 'category' || $this->view == 'items')) {

			if ($this->format == 'raw') {
				return array('class' => 'ph-lazyload', 'script' => ''); // return only class, don't load library in ajax
			}

			// Firefox ignores async - we need to load it at the bottom of the page
			//$this->document->addCustomTag(implode("\n", $s));
			//return 'ph-lazyload';
			return array('class' => 'ph-lazyload', 'script' => implode("\n", $s));

		} else if ($this->p['lazy_load_categories'] == 1 && $this->view == 'categories') {

			if ($this->format == 'raw') {
				return array('class' => 'ph-lazyload', 'script' => ''); // return only class, don't load library in ajax
			}

			// Firefox ignores async - we need to load it at the bottom of the page
			//$this->document->addCustomTag(implode("\n", $s));
			//return 'ph-lazyload';
			return array('class' => 'ph-lazyload', 'script' => implode("\n", $s));
		}

		return array();
	}

	public function loadProductHover() {
		if ($this->p['fade_in_action_icons'] == 1) {
			JHtml::stylesheet('media/com_phocacart/css/main-product-hover.css' );
			return '';
		} else {
			return 'b-thumbnail';
		}
	}

	public function loadPhocaMoveImage($load = 0) {
		if ($load == 1) {
			JHtml::stylesheet('media/com_phocacart/css/main-product-image-move.css' );
		}
	}

	public function loadBootstrap() {
		if ($this->p['load_bootstrap'] == 1) {

			JHtml::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
	}

	public function loadWindowPopup() {
		$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/windowpopup.js');
	}

	public function loadChosen() {
		if ($this->p['load_chosen'] == 2) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
		if ($this->p['load_chosen'] == 1 || $this->p['load_chosen'] == 2) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/chosen/chosen.jquery.min.js');
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/chosen/chosen.required.js');
			$js = "\n". 'jQuery(document).ready(function(){'."\n";
			$js .= '   jQuery(".chosen-select").chosen({disable_search_threshold: 10});'."\n"; // Set chosen, created hidden will be required
			// When select box is required, display the error message (when value not selected)
			// But on mobiles, this hide standard select boxes
			// we need to have condition, if really chosen is applied:
			// https://github.com/harvesthq/chosen/issues/1582
			//$js .= '   jQuery(".chosen-select").attr(\'style\',\'display:visible; position:absolute; clip:rect(0,0,0,0)\');'."\n";
			$js .= '});'."\n";
			$this->document->addScriptDeclaration($js);
			JHtml::stylesheet( 'media/com_phocacart/js/chosen/chosen.css' );
			JHtml::stylesheet( 'media/com_phocacart/js/chosen/chosen-bootstrap.css' );
		}
	}


	public function loadTouchSpin($name) {

		if ($this->p['quantity_input_spinner'] > 0) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/touchspin/jquery.bootstrap-touchspin.js');
			JHtml::stylesheet( 'media/com_phocacart/js/touchspin/jquery.bootstrap-touchspin.css' );

			$jsS = "\n". 'jQuery(document).ready(function(){'."\n";
			$js = '   jQuery("input[name=\''.$name.'\']:visible").TouchSpin({';
			$js .= '   verticalbuttons: true,';
			if ($this->p['quantity_input_spinner'] == 2) {
				$js .= '   verticalup: \'<span class="'.PhocacartRenderIcon::getClass('chevron-up').'"></span>\',';
				$js .= '   verticaldown: \'<span class="'.PhocacartRenderIcon::getClass('chevron-down').'"></span>\',';
			} else {
				$js .= '   verticalup: \'<span class="'.PhocacartRenderIcon::getClass('plus').'"></span>\',';
				$js .= '   verticaldown: \'<span class="'.PhocacartRenderIcon::getClass('minus').'"></span>\',';
			}
			//$js .= '   verticalupclass: "'.PhocacartRenderIcon::getClass('chevron-up').'",';
			//$js .= '   verticaldownclass: "'.PhocacartRenderIcon::getClass('chevron-down').'"';
			$js .= ' })';
			$jsE = '});'."\n";
			$this->document->addScriptDeclaration($jsS . $js . $jsE);

			return $js;
		}
	}

	public function loadSwiper(){
		$document					= JFactory::getDocument();
		$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/swiper/swiper.min.js');
		//$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/ui/jquery-ui.min.js');
		JHtml::stylesheet('media/com_phocacart/js/swiper/swiper.min.css' );
		//JHtml::stylesheet('media/com_phocacart/js/swiper/swiper-custom.css' );
	}



	public function loadEqualHeights() {

		if ($this->p['equal_height'] == 1) {
			return 'row-flex';
		} else {
			return '';
		}

		/*if ($load == 1) {

			//$app			= JFactory::getApplication();
			//$paramsC 		= PhocacartUtils::getComponentParameters();
			//$equal_height_method	= $paramsC->get( 'equal_height_method', 1 );
			$equal_height_method 	= 0;// FLEXBOX USED

			if ($equal_height_method == 1) {
				$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.equal.heights.js');
				//$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.equalminheights.min.js');
				$this->document->addScriptDeclaration(
				'jQuery(window).load(function(){
					jQuery(\'.ph-thumbnail-c\').equalHeights();
				});');
			} else if ($equal_height_method == 2) {
				$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.matchheight.min.js');
				$this->document->addScriptDeclaration(
				'jQuery(window).load(function(){
					jQuery(\'.ph-thumbnail-c\').matchHeight();
				});');
			} else if ($equal_height_method == 3) {

				$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.matchheight.min.js');
				$this->document->addScriptDeclaration(
				'jQuery(window).load(function(){
					jQuery(\'.ph-thumbnail-c.grid\').matchHeight({
					   byRow: false,
					   property: \'height\',
					   target: null,
					   remove: false
				    });
				});');
			}
			// not ph-thumbnail but only ph-thumbnail-c (in component area so module will not influence it)
		}*/
	}


	public function loadPhocaSwapImage() {
		if ($this->p['dynamic_change_image'] == 1) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/phoca/jquery.phocaswapimage.js');
		}
	}


	public function loadPhocaAttribute($load = 0) {
		if ($load == 1) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/phoca/jquery.phocaattribute.js');
		}
	}

	public function loadPhocaAttributeRequired($load = 0) {
		if ($load == 1) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/phoca/jquery.phocaattributerequired.js');
		}
	}

	public function loadRating() {
		JHtml::stylesheet( 'media/com_phocacart/js/barrating/css/rating.css' );
		$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/barrating/jquery.barrating.js');
		$js = "\n". 'jQuery(document).ready(function(){'."\n";
		$js .= 	'   jQuery(\'#phitemrating\').barrating({ showSelectedRating:false });'."\n";
		$js .= '});'."\n";
		$this->document->addScriptDeclaration($js);
	}
}
?>
