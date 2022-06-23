<?php
/**
 * @package    phocaguestbook
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
use Joomla\String\StringHelper;
//JFormHelper::loadRuleClass('email');

class JFormRulePhocaCartFile extends FormRule
{

	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{



		$app = Factory::getApplication();
		//E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
		$info = array();
		$info['field'] = 'phocacart_email';
		$params = PhocacartUtils::getComponentParameters();

		//EMAIL FORMAT
		if(!parent::test($element, $value, $group, $input, $form)){

			$app->enqueueMessage(Text::_('COM_PHOCACART_BAD_EMAIL' ), 'warning');
			return false;
		}

		//BANNED EMAIL
		$banned = $params->get('banned_email');
		foreach(explode(';', $banned) as $item){
			if (trim($item) != '') {
				if (StringHelper::stristr($item, $value) !== false){

					$app->enqueueMessage(Text::_('COM_PHOCACART_BAD_EMAIL' ), 'warning');
					return false;
				}
			}

			return true;
		}
	}
}
