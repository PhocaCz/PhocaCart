<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\HTML\HTMLHelper;

defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartEditBulkprice extends HtmlView
{
	protected $t;
	protected $r;
	protected $item;
	protected $itemhistory;
	protected $id;
	protected $type;
	function display($tpl = null) {

		$app	= Factory::getApplication();
		$id		= $app->input->get('id', 0, 'int');
		$status	= $app->input->get('status', 0, 'int');// 0 inactive - make run, 1 active - make revert


		Factory::getDocument()->addScriptOptions('phLang', array(
			'COM_PHOCACART_CLOSE' => Text::_('COM_PHOCACART_CLOSE'))
		);


		Factory::getDocument()->addScriptOptions('phVars', array('token' => Session::getFormToken(), 'urlbulkprice' => Uri::base(true).'/index.php?option=com_phocacart&format=json&'. Session::getFormToken().'=1'));

		HTMLHelper::_('jquery.framework', false);
		HTMLHelper::_('script', 'media/com_phocacart/js/administrator/phocacartbulkprice.js', array('version' => 'auto'));


		$this->r				= new PhocacartRenderAdminview();
		$this->item				= PhocacartPriceBulkprice::getItem($id);
		if ($status == 0) {
			$this->t				= PhocacartUtils::setVars('run');
		} else if($status == 1) {
			$this->t				= PhocacartUtils::setVars('revert');
		} else {
			echo '<div>'.Text::_('COM_PHOCACART_ERROR_NO_STATUS_DEFINED').'</div>';
			return;
		}




		$media = new PhocacartRenderAdminmedia();

		parent::display($tpl);
	}
}
?>
