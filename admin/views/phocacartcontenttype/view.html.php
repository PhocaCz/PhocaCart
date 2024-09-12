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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class PhocaCartCpViewPhocacartContentType extends HtmlView
{
	protected $state;
	protected $item;
	protected $form;
	protected $t;
	protected $r;
	protected $attributeoption;

	public function display($tpl = null)
    {
		$this->t		= PhocacartUtils::setVars('contenttype');
		$this->r		= new PhocacartRenderAdminview();
		$this->state	= $this->get('State');
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');

		new PhocacartRenderAdminmedia();

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
        require_once JPATH_COMPONENT.'/helpers/phocacartcommon.php';

		$user		= Factory::getApplication()->getIdentity();
		$isNew		= !$this->item->id;
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
		$canDo		= PhocaCartCommonHelper::getActions($this->t, $this->item->id);

		$text = $isNew ? Text::_('COM_PHOCACART_NEW' ) : Text::_('COM_PHOCACART_EDIT');
		ToolbarHelper::title(   Text::_('COM_PHOCACART_CONTENT_TYPE' ).': <small><small>[ ' . $text.' ]</small></small>' , 'sourcetree');

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit')){
			ToolbarHelper::apply('phocacartcontenttype.apply');
			ToolbarHelper::save('phocacartcontenttype.save');
			ToolbarHelper::addNew('phocacartcontenttype.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}

		if (empty($this->item->id))  {
			ToolbarHelper::cancel('phocacartcontenttype.cancel');
		} else {
			ToolbarHelper::cancel('phocacartcontenttype.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::inlinehelp();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}
}

