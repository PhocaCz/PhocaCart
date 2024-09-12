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
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view');
class PhocaCartViewInfo extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $u;
	protected $s;

	function display($tpl = null) {

		$document					= Factory::getDocument();
		$app						= Factory::getApplication();
		$uri 						= Uri::getInstance();
		$this->u					= PhocacartUser::getUser();
		$this->p					= $app->getParams();
		$this->s                    = PhocacartRenderStyle::getStyles();

		$this->t['info_view_description']			= $this->p->get( 'info_view_description', '' );
		$this->t['info_view_description']			= PhocacartRenderFront::renderArticle($this->t['info_view_description']);
		$this->t['guest_checkout']					= $this->p->get( 'guest_checkout', 0);

		$session 				= Factory::getSession();
		$this->t['infoaction'] 	= $session->get('infoaction', 0, 'phocaCart');
		$this->t['infomessage'] = $session->get('infomessage', array(), 'phocaCart');
		$this->t['infodata'] 	= $session->get('infodata', array(), 'phocaCart');

		$this->t['display_order_data'] = false;
		if (isset($this->t['infodata']['order_id']) && (int)$this->t['infodata']['order_id'] > 0) {

			if (isset($this->t['infodata']['user_id']) && isset($this->u->id ) && (int)$this->t['infodata']['user_id'] == (int)$this->u->id) {
				$this->t['display_order_data'] = true;
			} else if((int)$this->t['guest_checkout'] == 1 && isset($this->u->guest) &&  $this->u->guest == 1){
				$this->t['display_order_data'] = true;
			}
		}

		$this->t['preparereplace'] = false;
		if ($this->t['display_order_data']) {
			$this->t['order']			= new PhocacartOrderView();
			$this->t['order_id']		= (int)$this->t['infodata']['order_id'];
			$this->t['order_common']	= $this->t['order']->getItemCommon($this->t['order_id']);
			$this->t['order_bas']		= $this->t['order']->getItemBaS($this->t['order_id'], 1);

			$this->t['preparereplace']    = PhocacartText::prepareReplaceText($this->t['order'], $this->t['order_id'], $this->t['order_common'], $this->t['order_bas']);
		}


		$session->set('infoaction', 0, 'phocaCart');
		$session->set('infomessage', array(), 'phocaCart');
		$session->set('infodata', array(), 'phocaCart');// order_id, order_token, payment_id, payment_method, shipping_id, shipping_method, user_id


		$media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
		$media->loadSpec();


		$this->_prepareDocument();
		parent::display($tpl);
	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_INFO'));
	}
}
?>
