<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartOrderView extends HtmlView
{
	public function display($tpl = null) {

		$app			= Factory::getApplication();
		$this->t		= PhocacartUtils::setVars('orderview');
		$this->r		= new PhocacartRenderAdminview();
		$id				= $app->input->get('id', 0, 'int');
		$type			= $app->input->get('type', 0, 'int');
		$format			= $app->input->get('format', '', 'string');

		$order	= new PhocacartOrderRender();
		$o = $order->render($id, $type, $format);
		echo $o;



		$media = new PhocacartRenderAdminmedia();

		parent::display($tpl);
	}

}
?>
