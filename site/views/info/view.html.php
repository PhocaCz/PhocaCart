<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');
class PhocaCartViewInfo extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;
	
	function display($tpl = null) {
				
		$document					= JFactory::getDocument();		
		$app						= JFactory::getApplication();
		$uri 						= JFactory::getURI();
		$this->u					= JFactory::getUser();
		$this->p					= $app->getParams();
		
		$session 				= JFactory::getSession();
		$this->t['infoaction'] 	= $session->get('infoaction', 0, 'phocaCart');
		$this->t['infomessage'] = $session->get('infomessage', array(), 'phocaCart');
		$session->set('infoaction', 0, 'phocaCart');
		$session->set('infomessage', array(), 'phocaCart');
		
		$media = new PhocacartRenderMedia();
		
		$this->_prepareDocument();
		parent::display($tpl);
	}
	
	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, JText::_('COM_PHOCACART_INFO'));
	}
}
?>