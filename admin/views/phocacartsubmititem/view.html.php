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
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartSubmititem extends HtmlView
{
	protected $state;
	protected $item;
	protected $form;
	protected $t;
	protected $r;
	protected $p;
	protected $s;
	protected $attributes;

	public function display($tpl = null) {

		$this->t		= PhocacartUtils::setVars('submititem');
		$this->r		= new PhocacartRenderAdminview();
		$this->state	= $this->get('State');
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');

		$this->p 		= PhocacartUtils::getComponentParameters();
		$this->s        = PhocacartRenderStyle::getStyles();

		// Items and Items (Contact) are defined in this view
		// Items (Parameters) will be defined model (when creating the form)

		$this->t['submit_item_form_fields']	= $this->p->get( 'submit_item_form_fields', '' );
		//$this->t['items'] = explode(',', $this->t['submit_item_form_fields']);
		$this->t['items_item'] = array_map('trim', explode(',', $this->t['submit_item_form_fields']));
		$this->t['items_item'] = array_unique($this->t['items_item']);

		$this->t['submit_item_form_fields_parameters']	= $this->p->get( 'submit_item_form_fields_parameters', '' );
		$this->t['items_parameter'] = array_map('trim', explode(',', $this->t['submit_item_form_fields_parameters']));
		$this->t['items_parameter'] = array_unique($this->t['items_parameter']);

		$this->t['submit_item_form_fields_contact']	= $this->p->get( 'submit_item_form_fields_contact', '' );
		$this->t['items_contact'] = array_map('trim', explode(',', $this->t['submit_item_form_fields_contact']));
		$this->t['items_contact'] = array_unique($this->t['items_contact']);



		$media = new PhocacartRenderAdminmedia();




		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		Factory::getApplication()->input->set('hidemainmenu', true);
		$bar 		= Toolbar::getInstance('toolbar');
		$user		= Factory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$class		= ucfirst($this->t['tasks']).'Helper';
		$canDo		= $class::getActions($this->t, $this->state->get('filter.item_id'));

		$text = $isNew ? Text::_( $this->t['l'] . '_NEW' ) : Text::_($this->t['l'] . '_EDIT');
		ToolbarHelper::title(   Text::_( $this->t['l'] . '_SUBMITTED_ITEM' ).': <small><small>[ ' . $text.' ]</small></small>' , 'duplicate-alt');

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit')){
			ToolbarHelper::apply($this->t['task'] . '.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save($this->t['task'] . '.save', 'JTOOLBAR_SAVE');
			ToolbarHelper::addNew($this->t['task'] . '.save2new', 'JTOOLBAR_SAVE_AND_NEW');

		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			//JToolbarHelper::custom($this->t.'.save2copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}


		if (empty($this->item->id))  {
			ToolbarHelper::cancel($this->t['task'] . '.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			ToolbarHelper::cancel($this->t['task'] . '.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );

	}
}
?>
