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
class PhocaCartViewResponse extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;
	
	function display($tpl = null) {
		
		$document					= JFactory::getDocument();		
		$app						= JFactory::getApplication();
		$uri 						= \Joomla\CMS\Uri\Uri::getInstance();
		$this->u					= PhocacartUser::getUser();
		$this->p					= $app->getParams();
		
		$this->_prepareDocument();
		parent::display($tpl);
	}
	
	protected function _prepareDocument() {
		//PhocacartRenderFront::prepareDocument($this->document, $this->p);
	}
}
?>