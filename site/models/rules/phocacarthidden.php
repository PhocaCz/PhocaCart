<?php
/**
 * @package    phocacart
 * @subpackage Models
 * @copyright  Copyright (C) 2012 Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

class JFormRulePhocaCartHidden extends JFormRule
{

	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{
		
		$app = JFactory::getApplication();
		//E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
		$info = array();
		$info['field'] = 'phocacart_hidden';
		//Get POST Data - - - - - - - - - 
		if ($value != '') {
			
			
			$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'error');
			return false;
		}
		
		return true;
	}
}
