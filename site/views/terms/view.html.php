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
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view');

class PhocaCartViewTerms extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $u;
	protected $s;

	public function display($tpl = null) {

		$app						= Factory::getApplication();
		$this->p 					= $app->getParams();
		$this->s                    = PhocacartRenderStyle::getStyles();
		$this->t['terms_conditions']= $this->p->get( 'terms_conditions', '' );
		$this->t['terms_conditions']= PhocacartRenderFront::renderArticle($this->t['terms_conditions']);

		$media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
		$media->loadSpec();

		$this->_prepareDocument();
		parent::display($tpl);

	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_TERMS_AND_CONDITIONS'));
	}
}
?>
