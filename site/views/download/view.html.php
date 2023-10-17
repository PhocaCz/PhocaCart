<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view');

class PhocaCartViewDownload extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $u;
	protected $s;

	function display($tpl = null)
	{
		$app								= Factory::getApplication();
		//$model								= $this->getModel();
		//$document							= Factory::getDocument();
		$this->s                            = PhocacartRenderStyle::getStyles();
		$this->p 							= $app->getParams();
		$this->u							= PhocacartUser::getUser();
		$this->t['token_download']			= $app->input->get('d', '', 'string');
		$this->t['token_order']				= $app->input->get('o', '', 'string');
		$this->t['download_guest_access']	= $this->p->get( 'download_guest_access', 0 );
		if ($this->t['download_guest_access'] == 0) {
			$this->t['token_download'] = '';
			$this->t['token_order'] = '';
		}
		$this->t['files']					= PhocacartDownload::getDownloadFiles($this->u->id, $this->t['token_download'], $this->t['token_order'] );

		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		$this->t['download_days']			= $this->p->get( 'download_days', 0 );
		$this->t['download_count']			= $this->p->get( 'download_count', 0 );

		$uri 						= Uri::getInstance();
		$this->t['action']			= $uri->toString();
		$this->t['actionbase64']	= base64_encode($this->t['action']);
		$this->t['linkdownload']	= Route::_(PhocacartRoute::getDownloadRoute());

		$media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
		$media->loadSpec();

		$this->t['pathfile'] = PhocacartPath::getPath('productfile');
		$this->_prepareDocument();
		parent::display($tpl);

	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_DOWNLOAD'));
	}
}
?>
