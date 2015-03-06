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

class PhocaCartViewOrders extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;

	function display($tpl = null)
	{		
		$app								= JFactory::getApplication();
		$this->u							= JFactory::getUser();
		$document							= JFactory::getDocument();
		$this->p 							= $app->getParams();
		$model								= $this->getModel();
		$this->t['orders']					= $model->getOrderList();
		
		$this->t['token']					= $app->input->get('o', '', 'string');
		$this->t['load_bootstrap']			= $this->p->get( 'load_bootstrap', 0 );
		$this->t['order_guest_access']		= $this->p->get( 'order_guest_access', 0 );
		if ($this->t['order_guest_access'] == 0) {
			$this->t['token'] = '';
		}
		
		/*$app								= JFactory::getApplication();
		
		$document							= JFactory::getDocument();
		$this->p 							= $app->getParams();	
		$this->t['categories']				= $model->getCategoriesList();

		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		$this->t['description']				= $this->p->get( 'description', '' );
		$this->t['load_bootstrap']			= $this->p->get( 'load_bootstrap', 0 );
		$this->t['equal_height']			= $this->p->get( 'equal_height', 1 );
		$this->t['columns_cats']			= $this->p->get( 'columns_cats', 3 );
		$this->t['image_width_cats']		= $this->p->get( 'image_width_cats', '' );
		$this->t['image_height_cats']		= $this->p->get( 'image_height_cats', '' );
		$this->t['display_subcat_cats_view']= $this->p->get( 'display_subcat_cats_view', 3 );
		*/
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		if ($this->t['load_bootstrap'] == 1) {
			JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
			$document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
		/*
		if ($this->t['equal_height'] == 1) {
			JHtml::_('jquery.framework', false);
			$document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.equalheights.min.js');
			/*$document->addScriptDeclaration(
			'jQuery(document).ready(function(){
				jQuery(\'.ph-thumbnail\').equalHeights();
			});');*//*
			
			$document->addScriptDeclaration(
			'jQuery(window).load(function(){
				jQuery(\'.ph-thumbnail\').equalHeights();
			});');
			
			
		}
		
		$this->t['path'] = PhocaCartPath::getPath('categoryimage');*/
		$this->_prepareDocument();
		parent::display($tpl);
		
	}
	
	protected function _prepareDocument() {
		PhocaCartRenderFront::prepareDocument($this->document, $this->p);
	}
	
}
?>