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

class PhocaCartViewCategory extends JViewLegacy
{
	protected $category;
	protected $subcategories;
	protected $items;
	protected $t;
	protected $p;
	
	function display($tpl = null) {		
		
		$app						= JFactory::getApplication();
		$this->p 					= $app->getParams();
		$uri 						= JFactory::getURI();
		$model						= $this->getModel();
		$document					= JFactory::getDocument();
		$this->t['categoryid']		= $app->input->get( 'id', 0, 'int' );
		$this->t['limitstart']		= $app->input->get( 'limitstart', 0, 'int' );
		
		$this->category				= $model->getCategory($this->t['categoryid']);
		$this->subcategories		= $model->getSubcategories($this->t['categoryid']);
		$this->items				= $model->getItemList($this->t['categoryid']);
		$this->t['pagination']		= $model->getPagination($this->t['categoryid']);
		$this->t['ordering']		= $model->getOrdering();
		
		$this->t['photopathrel']	= JURI::base().'phocaphoto/';
		$this->t['photopathabs']	= JPATH_ROOT .'/phocaphoto/';
		$this->t['action']			= $uri->toString();
		//$this->t['actionbase64']	= base64_encode(htmlspecialchars($this->t['action']));
		$this->t['actionbase64']	= base64_encode($this->t['action']);
		$this->t['linkcheckout']	= JRoute::_(PhocaCartRoute::getCheckoutRoute(0, (int)$this->t['categoryid']));
		$this->t['linkcomparison']	= JRoute::_(PhocaCartRoute::getComparisonRoute(0, (int)$this->t['categoryid']));
		
		
		if ($this->t['limitstart'] > 0 ) {
			$this->t['limitstarturl'] =  '&start='.$this->t['limitstart'];
		} else {
			$this->t['limitstarturl'] = '';
		}
		
		$this->t['display_new']				= $this->p->get( 'display_new', 0 );
		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		//$this->t['description']			= $this->p->get( 'description', '' );
		$this->t['load_bootstrap']			= $this->p->get( 'load_bootstrap', 0 );
		$this->t['equal_height']			= $this->p->get( 'equal_height', 0 );
		$this->t['image_width_cat']			= $this->p->get( 'image_width_cat', '' );
		$this->t['image_height_cat']		= $this->p->get( 'image_height_cat', '' );
		//$this->t['image_link']				= $this->p->get( 'image_link', 0 );
		$this->t['columns_cat']				= $this->p->get( 'columns_cat', 3 );
		$this->t['enable_social']			= $this->p->get( 'enable_social', 0 );
		$this->t['cv_display_subcategories']= $this->p->get( 'cv_display_subcategories', 5 );
		$this->t['display_back']			= $this->p->get( 'display_back', 3 );
		$this->t['display_compare']			= $this->p->get( 'display_compare', 0 );
		$this->t['display_addtocart']		= $this->p->get( 'display_addtocart', 1 );
		
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		if ($this->t['load_bootstrap'] == 1) {
			JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
			$document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
		
		if ($this->t['equal_height'] == 1) {
			JHtml::_('jquery.framework', false);
			$document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.equalheights.min.js');
			$document->addScriptDeclaration(
			'jQuery(document).ready(function(){
				jQuery(\'.ph-thumbnail\').equalHeights();
			});');
		}
		
		/*JHTML::stylesheet( 'media/com_phocaphoto/js/prettyphoto/css/prettyPhoto.css' );
		$document->addScript(JURI::root(true).'/media/com_phocaphoto/js/prettyphoto/js/jquery.prettyPhoto.js');
		
		$js = "\n". 'jQuery(document).ready(function(){
			jQuery("a[rel^=\'prettyPhoto\']").prettyPhoto({'."\n";
		if ($this->t['enable_social'] == 0) {
			$js .= '  social_tools: '.(int)$this->t['enable_social'].''."\n";
		}		
		$js .= '  });
		});'."\n";
		$document->addScriptDeclaration($js);*/

		$this->_prepareDocument();
		$this->t['pathcat'] = PhocaCartPath::getPath('categoryimage');
		$this->t['pathitem'] = PhocaCartpath::getPath('productimage');

		parent::display($tpl);
		
	}
	

	protected function _prepareDocument() {
		$category = false;
		if (isset($this->category[0]) && is_object($this->category[0])) {
			$category = $this->category[0];
		}
		PhocaCartRenderFront::prepareDocument($this->document, $this->p, $category);
	}
}
?>