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
jimport( 'joomla.itemsystem.folder' ); 
jimport( 'joomla.itemsystem.file' );

class PhocaCartViewItem extends JViewLegacy
{
	protected $item;
	protected $itemnext;
	protected $itemprev;
	protected $category;
	protected $t;
	protected $p;
	protected $u;

	function display($tpl = null){		
		
		$app					= JFactory::getApplication();
		$this->p 				= $app->getParams();
		$this->u				= JFactory::getUser();
		$uri 					= JFactory::getURI();
		$model					= $this->getModel();
		$document				= JFactory::getDocument();
		$id						= $app->input->get('id', 0, 'int');

		$this->category					= $model->getCategory($id);
		$this->item						= $model->getItem($id);
		$this->t['add_images']			= PhocaCartImage::getAdditionalImages((int)$id);
		$this->t['rel_products']		= PhocaCartRelated::getRelatedItemsById((int)$id);
		$this->t['tags_output']			= PhocaCartTag::getTagsRendered((int)$id);
		$this->t['stock_status']		= PhocaCartStock::getStockStatus((int)$this->item[0]->stock, (int)$this->item[0]->min_quantity, (int)$this->item[0]->stockstatus_a_id,  (int)$this->item[0]->stockstatus_n_id);
		$this->t['stock_status_output'] = PhocaCartStock::getStockStatusOutput($this->t['stock_status']);
		$this->t['attr_options']		= PhocaCartAttribute::getAttributesAndOptions((int)$id);
		$this->t['specifications']		= PhocaCartSpecification::getSpecificationGroupsAndSpecifications((int)$id);
		$this->t['reviews']				= PhocaCartReview::getReviewsByProduct((int)$id);
	
		$this->t['action']				= $uri->toString();
		//$this->t['actionbase64']		= base64_encode(htmlspecialchars($this->t['action']));
		$this->t['actionbase64']		= base64_encode($this->t['action']);
		$this->t['linkcheckout']		= JRoute::_(PhocaCartRoute::getCheckoutRoute((int)$this->item[0]->id, (int)$this->category[0]->id));
		$this->t['linkitem']			= JRoute::_(PhocaCartRoute::getItemRoute((int)$this->item[0]->id, (int)$this->category[0]->id));

		$this->t['tax_calculation'] 		= $this->p->get( 'tax_calculation', 0 );
		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		$this->t['load_bootstrap']			= $this->p->get( 'load_bootstrap', 0 );
		$this->t['display_back']			= $this->p->get( 'display_back', 3 );
		//$this->t['enable_social']			= $this->p->get( 'enable_social', 0 );
		$this->t['enable_item_navigation']	= $this->p->get( 'enable_item_navigation', 0 );
		$this->t['display_addtocart']		= $this->p->get( 'display_addtocart', 1 );
		
		
		$this->itemnext[0]			= false;
		$this->itemprev[0]			= false;
		if ($this->t['enable_item_navigation'] == 1) {
			if (isset($this->item[0]->ordering) && isset($this->item[0]->catid) && isset($this->item[0]->id) && $this->item[0]->catid > 0 && $this->item[0]->id > 0) {
				$this->itemnext			= $model->getItemNext($this->item[0]->ordering, $this->item[0]->catid);
				$this->itemprev			= $model->getItemPrev($this->item[0]->ordering, $this->item[0]->catid);
			}
		}
		
		
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		JHtml::_('jquery.framework', false);
		if ($this->t['load_bootstrap'] == 1) {
			//JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
			//$document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
		$document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/chosen/chosen.jquery.min.js');
		$js = "\n". 'jQuery(document).ready(function(){';

		$js .= '   jQuery(".chosen-select").chosen({disable_search_threshold: 10});'."\n"; // Set chosen, created hidden will be required
		$js .= '   jQuery(".chosen-select").attr(\'style\',\'display:visible; position:absolute; clip:rect(0,0,0,0)\');'."\n";
		$js .= '});'."\n";
		$document->addScriptDeclaration($js);
		JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen.css' );
		JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen-bootstrap.css' );
		
		JHTML::stylesheet( 'media/com_phocacart/js/prettyphoto/css/prettyPhoto.css' );
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/prettyphoto/js/jquery.prettyPhoto.js');
		
		$js = "\n". 'jQuery(document).ready(function(){
			jQuery("a[rel^=\'prettyPhoto\']").prettyPhoto({'."\n";
		$js .= '  social_tools: 0'."\n";		
		$js .= '  });
		});'."\n";

		$document->addScriptDeclaration($js);
	
		JHTML::stylesheet( 'media/com_phocacart/js/barrating/css/rating.css' );
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/barrating/jquery.barrating.js');
		$js = "\n". 'jQuery(document).ready(function(){'."\n";
		$js .= 	'   jQuery(\'#phitemrating\').barrating({ showSelectedRating:false });'."\n";		
		$js .= '});'."\n";
		$document->addScriptDeclaration($js);

		
		if (isset($this->category[0]) && is_object($this->category[0]) && isset($this->item[0]) && is_object($this->item[0])){
			$this->_prepareDocument($this->category[0], $this->item[0]);
		}
		
		$this->t['pathitem'] = PhocaCartpath::getPath('productimage');
		parent::display($tpl);
		
	}
	

	
	protected function _prepareDocument() {
		$category = false;
		if (isset($this->category[0]) && is_object($this->category[0])) {
			$category = $this->category[0];
		}
		$item = false;
		if (isset($this->item[0]) && is_object($this->item[0])) {
			$item = $this->item[0];
		}
		PhocaCartRenderFront::prepareDocument($this->document, $this->p, $category, $item);
	}
}
?>