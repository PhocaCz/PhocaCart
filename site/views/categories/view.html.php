<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');

class PhocaCartViewCategories extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $s;

	public function display($tpl = null) {
		

		$app									= JFactory::getApplication();
		$model									= $this->getModel();
		$document								= JFactory::getDocument();
		$this->p 								= $app->getParams();
		$this->s								= PhocacartRenderStyle::getStyles();

		$this->t['csv_display_subcategories']	= $this->p->get( 'csv_display_subcategories', 0 );
		$this->t['categories']					= $model->getCategoriesList($this->t['csv_display_subcategories']);
		$this->t['csv_display_category_desc']	= $this->p->get( 'csv_display_category_desc', 0 );
		$this->t['cart_metakey'] 				= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 				= $this->p->get( 'cart_metadesc', '' );
		$this->t['main_description']			= $this->p->get( 'main_description', '' );
		$this->t['main_description']			= PhocacartRenderFront::renderArticle($this->t['main_description']);

		$this->t['image_width_cats']			= $this->p->get( 'image_width_cats', '' );
		$this->t['image_height_cats']			= $this->p->get( 'image_height_cats', '' );
		$this->t['display_view_category_button']= $this->p->get( 'display_view_category_button', 1 );
		$this->t['category_name_link']			= $this->p->get( 'category_name_link', 0 );
		$this->t['categories_view_layout']		= $this->p->get( 'categories_view_layout', 1 );

		$this->t['lazy_load_categories']		= $this->p->get( 'lazy_load_categories', 0 );
		$this->t['medium_image_width']			= $this->p->get( 'medium_image_width', 300 );
		$this->t['medium_image_height']			= $this->p->get( 'medium_image_height', 200 );
		$this->t['display_webp_images']			= $this->p->get( 'display_webp_images', 0 );



		$this->t['class_row_flex']              = $this->p->get('equal_height', 1)  == 1 ? 'ph-row-flex' : '';
        $this->t['class_fade_in_action_icons']  = $this->p->get('fade_in_action_icons', 0)  == 1 ? 'b-thumbnail' : '';
        $this->t['class_lazyload']       		= $this->t['lazy_load_categories']  == 1 ? 'ph-lazyload' : '';


        $media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
		$media->loadChosen();


		$this->t['path'] = PhocacartPath::getPath('categoryimage');

		// Plugins ------------------------------------------
		JPluginHelper::importPlugin('pcv');
		//$this->t['dispatcher'] 	= J EventDispatcher::getInstance();
		$this->t['event']		= new stdClass;

		$results = \JFactory::getApplication()->triggerEvent('PCVonCategoriesBeforeHeader', array('com_phocacart.categories', &$this->t['categories'], &$this->p));

		$this->t['event']->onCategoriesBeforeHeader = trim(implode("\n", $results));
		// END Plugins --------------------------------------

        $media->loadSpec();
		$this->_prepareDocument();
		parent::display($tpl);

		echo $media->returnLazyLoad();// Render all bottom scripts // Must be loaded bottom because of ignoring async in Firefox

	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p);
	}
}
?>
