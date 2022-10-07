<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartWizard extends HtmlView
{
	protected $t;
	protected $r;
	protected $s;
	protected $page;

	function display($tpl = null) {

		$app				    = Factory::getApplication();
		$this->page		        = $app->input->get('page', 0, 'int');
		$this->t				= PhocacartUtils::setVars('wizard');
		$this->r				= new PhocacartRenderAdminview();
		$this->s                = PhocacartRenderStyle::getStyles();


		$media = new PhocacartRenderAdminmedia();

		parent::display($tpl);
	}
}
?>
