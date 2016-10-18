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

class PhocaCartViewDownload extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;

	function display($tpl = null)
	{		
		$app								= JFactory::getApplication();
		$model								= $this->getModel();
		$document							= JFactory::getDocument();
		$this->p 							= $app->getParams();
		$this->u							= JFactory::getUser();
		$this->t['token_download']			= $app->input->get('d', '', 'string');
		$this->t['token_order']				= $app->input->get('o', '', 'string');
		$this->t['download_guest_access']	= $this->p->get( 'download_guest_access', 0 );
		if ($this->t['download_guest_access'] == 0) {
			$this->t['token_download'] = '';
			$this->t['token_order'] = '';
		}
		$this->t['files']					= PhocaCartDownload::getDownloadFiles($this->u->id, $this->t['token_download'], $this->t['token_order'] );

		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		$this->t['load_bootstrap']			= $this->p->get( 'load_bootstrap', 0 );
		$this->t['download_days']			= $this->p->get( 'download_days', 0 );
		$this->t['download_count']			= $this->p->get( 'download_count', 0 );
		/*$this->t['main_description']		= $this->p->get( 'main_description', '' );
		$this->t['equal_height']			= $this->p->get( 'equal_height', 1 );
		$this->t['columns_cats']			= $this->p->get( 'columns_cats', 3 );
		$this->t['image_width_cat']			= $this->p->get( 'image_width_cat', '' );
		$this->t['image_height_cat']		= $this->p->get( 'image_height_cat', '' );*/
		
		$uri 						= JFactory::getURI();
		$this->t['action']			= $uri->toString();
		$this->t['actionbase64']	= base64_encode($this->t['action']);
		$this->t['linkdownload']	= JRoute::_(PhocaCartRoute::getDownloadRoute());
		
		$media = new PhocaCartRenderMedia();
		$media->loadBootstrap($this->t['load_bootstrap']);
		//$media->loadChosen($this->t['load_chosen']);
		//$media->loadEqualHeights($this->t['equal_height']);
		
		$this->t['pathfile'] = PhocaCartPath::getPath('productfile');
		$this->_prepareDocument();
		parent::display($tpl);
		
	}
	
	protected function _prepareDocument() {
		PhocaCartRenderFront::prepareDocument($this->document, $this->p, false, false, JText::_('COM_PHOCACART_DOWNLOAD'));
	}
}
?>