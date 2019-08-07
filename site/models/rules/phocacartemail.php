<?php
/**
 * @package    phocaguestbook
 * @subpackage Models
 * @copyright  Copyright (C) 2012 Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

JFormHelper::loadRuleClass('email');

class JFormRulePhocaCartEmail extends JFormRuleEmail
{

	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{

		$app = JFactory::getApplication();
		//E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
		$info = array();
		$info['field'] = 'phocacart_email';
		$params = PhocacartUtils::getComponentParameters();

		//EMAIL FORMAT
		if(!parent::test($element, $value, $group, $input, $form)){

			$app->enqueueMessage(JText::_('COM_PHOCACART_BAD_EMAIL' ), 'warning');
			return false;
		}

		//BANNED EMAIL
		$banned = $params->get('banned_email');
		foreach(explode(';', $banned) as $item){
			if (trim($item) != '') {
				if (\Joomla\String\StringHelper::stristr($item, $value) !== false){

					$app->enqueueMessage(JText::_('COM_PHOCACART_BAD_EMAIL' ), 'warning');
					return false;
				}
			}

			return true;
		}
	}
}
