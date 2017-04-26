<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.view' );
 
class PhocaCartCpViewPhocaCartWizard extends JViewLegacy
{
	protected $t;
	protected $page;
	
	function display($tpl = null) {
		
		$app				= JFactory::getApplication();
		$this->page		= $app->input->get('page', 0, 'int');
		
		$this->t				= PhocacartUtils::setVars('');
		
		

		$media = new PhocacartRenderAdminmedia();
	
		parent::display($tpl);
	}
}
?>