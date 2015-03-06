<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartOrderView extends JViewLegacy
{
	public function display($tpl = null) {
		
		$app			= JFactory::getApplication();
		$this->t		= PhocaCartUtils::setVars('orderview');
		$id				= $app->input->get('id', 0, 'int');
		$type			= $app->input->get('type', 0, 'int');
		$format			= $app->input->get('format', 0, 'int');
		
		$order	= new PhocaCartOrderRender();
		$o = $order->render($id, $type, $format);
		echo $o;
		
		
		
		JHTML::stylesheet( $this->t['s'] );

		parent::display($tpl);	
	}
	
}
?>
