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

	public function display($tpl = null) {		
		
		$app									= JFactory::getApplication();
		$model									= $this->getModel();
		$document								= JFactory::getDocument();
		$this->p 								= $app->getParams();

		$this->t['csv_display_subcategories']	= $this->p->get( 'csv_display_subcategories', 0 );
		$this->t['categories']					= $model->getCategoriesList($this->t['csv_display_subcategories']);
		$this->t['csv_display_category_desc']	= $this->p->get( 'csv_display_category_desc', 0 );
		$this->t['cart_metakey'] 				= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 				= $this->p->get( 'cart_metadesc', '' );
		$this->t['main_description']			= $this->p->get( 'main_description', '' );
		$this->t['load_bootstrap']				= $this->p->get( 'load_bootstrap', 1 );
		$this->t['equal_height']				= $this->p->get( 'equal_height', 1 );
		$this->t['columns_cats']				= $this->p->get( 'columns_cats', 3 );
		$this->t['image_width_cats']			= $this->p->get( 'image_width_cats', '' );
		$this->t['image_height_cats']			= $this->p->get( 'image_height_cats', '' );
		
		$media = new PhocaCartRenderMedia();
		$media->loadBootstrap($this->t['load_bootstrap']);
		//$media->loadChosen($this->t['load_chosen']);
		$media->loadEqualHeights($this->t['equal_height']);
		
		$this->t['path'] = PhocaCartPath::getPath('categoryimage');
		
		$this->_prepareDocument();
		parent::display($tpl);
		
	}
	
	protected function _prepareDocument() {
		PhocaCartRenderFront::prepareDocument($this->document, $this->p);
	}
}
?>