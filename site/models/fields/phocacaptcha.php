<?php
/**
 * @package    phocaguestbook
 * @subpackage Models
 * @copyright  Copyright (C) 2012 Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die;

class JFormFieldPhocacaptcha extends JFormField
{
	protected $type 		= 'phocacaptcha';
		
	protected function getInput() {
		
		$document	= JFactory::getDocument();
		$session 	= JFactory::getSession();
		$params     = JComponentHelper::getParams('com_phocacart');
		$string 	= bin2hex(openssl_random_pseudo_bytes(10));
		$namespace	= 'pc'.$params->get('session_suffix', $string);
		$captchaCnt = $session->get('captcha_cnt',  0, $namespace) + 1;
						
		$id = $session->get('captcha_id', '', $namespace);
		
		switch ($id){
			default:
			case 1:
				$retval = PhocaCartRecaptcha::render();
			break;
		}

		return $retval;		
	}
	

}
?>
