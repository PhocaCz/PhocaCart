<?php
/**
 * @package    phocaguestbook
 * @subpackage Models
 * @copyright  Copyright (C) 2012 Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
 
class JFormRulePhocacartCaptcha extends JFormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{		
		//E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
		$info = array();
		$info['field'] = 'question_captcha';
		$params = JComponentHelper::getParams('com_phocacart');
		$session = JFactory::getSession();
		$namespace = 'phccrt'.$params->get('session_suffix');
		
		// Possible parameters in Options for different captchas
		$captchaId = 1;

		switch($captchaId) {
			case 1:
			default:
				if (!PhocacartCaptchaRecaptcha::isValid()) {
				
					// What happens when the CAPTCHA was entered incorrectly
					return new JException(JText::_('COM_PHOCACART_WRONG_CAPTCHA' ), "105", E_USER_ERROR, $info, false);
				}
				
				return true;
			break;
		}
		return false;
	}
}
