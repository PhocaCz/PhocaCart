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
use Joomla\CMS\Uri\Uri;
jimport( 'joomla.application.component.view');
class PhocaCartViewResponse extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $u;
	protected $s;

	function display($tpl = null) {

		$document					= Factory::getDocument();
		$app						= Factory::getApplication();
		$uri 						= Uri::getInstance();
		$this->u					= PhocacartUser::getUser();
		$this->p					= $app->getParams();
		$this->s                    = PhocacartRenderStyle::getStyles();

		$this->_prepareDocument();
		parent::display($tpl);
	}

	protected function _prepareDocument() {
		//PhocacartRenderFront::prepareDocument($this->document, $this->p);
	}
}
?>
