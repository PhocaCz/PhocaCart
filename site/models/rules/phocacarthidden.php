<?php
/**
 * @package    phocacart
 * @subpackage Models
 * @copyright  Copyright (C) 2012 Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class JFormRulePhocaCartHidden extends FormRule
{

	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		
		$app = Factory::getApplication();
		//E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
		$info = array();
		$info['field'] = 'phocacart_hidden';
		//Get POST Data - - - - - - - - - 
		if ($value != '') {
			
			
			$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'error');
			return false;
		}
		
		return true;
	}
}
