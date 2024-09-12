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
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view');

class PhocaCartViewOrders extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $u;
	protected $s;

	function display($tpl = null)
	{
		$app								= Factory::getApplication();
		$this->u							= PhocacartUser::getUser();
		//$document							= Factory::getDocument();
		$this->s							= PhocacartRenderStyle::getStyles();
		$this->p 							= $app->getParams();
		$model								= $this->getModel();
		$this->t['orders']					= $model->getOrderList();

		$this->t['token']					            = $app->input->get('o', '', 'string');
		$this->t['order_guest_access']		            = $this->p->get( 'order_guest_access', 0 );
        $this->t['display_reward_points_user_orders']	= $this->p->get( 'display_reward_points_user_orders', 0);
		if ($this->t['order_guest_access'] == 0) {
			$this->t['token'] = '';
		}

		/*$app								= Factory::getApplication();
		$document							= Factory::getDocument();
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

		$this->t['plugin-pdf']		= PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
		$this->t['component-pdf']	= PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');

		$media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
		$media->loadWindowPopup();
		$media->loadSpec();

		//$this->t['path'] = PhocacartPath::getPath('categoryimage');
		$this->_prepareDocument();
		parent::display($tpl);

	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_ORDERS'));
	}

}
?>
