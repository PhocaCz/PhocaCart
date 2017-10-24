<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormRulePhocaCartTitle extends JFormRule
{

	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		
		$app = JFactory::getApplication();
		//E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
		//$info = array();
		//$info['field'] = 'guestbook_title';
		
		//if (preg_match("~[<|>]~",$value)) {
		
		//}
	
		$app->enqueueMessage(JText::_('COM_PHOCACART_BAD_SUBJECT' ), 'warning');
		return false;
		

		return true;
	}
}
