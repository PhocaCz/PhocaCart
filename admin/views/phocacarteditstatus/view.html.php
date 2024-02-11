<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;

class PhocaCartCpViewPhocaCartEditStatus extends HtmlView
{
	protected $t;
	protected $r;
	protected $item;
	protected $itemhistory;
	protected $id;
    protected Form $form;

	function display($tpl = null) {

		$app				= Factory::getApplication();
		$this->id			= $app->input->get('id', 0, 'int');

		$this->t			= PhocacartUtils::setVars('cart');
		$this->r 			= new PhocacartRenderAdminviews();
		$this->item			= $this->get('Data');
		$this->itemhistory	= $this->get('HistoryData');
        $this->form			= $this->get('Form');

		new PhocacartRenderAdminmedia();

		parent::display($tpl);
	}
}
