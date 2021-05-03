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
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartEditBulkprice extends JViewLegacy
{
	protected $t;
	protected $r;
	protected $item;
	protected $itemhistory;
	protected $id;
	protected $type;
	function display($tpl = null) {

		$app	= JFactory::getApplication();
		$id		= $app->input->get('id', 0, 'int');
		$status	= $app->input->get('status', 0, 'int');// 0 inactive - make run, 1 active - make revert


		JFactory::getDocument()->addScriptOptions('phLang', array(
			'COM_PHOCACART_CLOSE' => JText::_('COM_PHOCACART_CLOSE'))
		);


		JFactory::getDocument()->addScriptOptions('phVars', array('token' => JSession::getFormToken(), 'urlbulkprice' => JURI::base(true).'/index.php?option=com_phocacart&format=json&'. JSession::getFormToken().'=1'));

		HTMLHelper::_('jquery.framework', false);
		HTMLHelper::_('script', 'media/com_phocacart/js/administrator/phocacartbulkprice.js', array('version' => 'auto'));


		$this->r				= new PhocacartRenderAdminview();
		$this->item				= PhocacartPriceBulkprice::getItem($id);
		if ($status == 0) {
			$this->t				= PhocacartUtils::setVars('run');
		} else if($status == 1) {
			$this->t				= PhocacartUtils::setVars('revert');
		} else {
			echo '<div>'.JText::_('COM_PHOCACART_ERROR_NO_STATUS_DEFINED').'</div>';
			return;
		}




		$media = new PhocacartRenderAdminmedia();

		parent::display($tpl);
	}
}
?>
