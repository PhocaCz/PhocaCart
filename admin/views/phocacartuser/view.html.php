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

class PhocaCartCpViewPhocacartUser extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;
	protected $formspecific;
	protected $fields;
	protected $t;
	protected $r;
	protected $u;

	public function display($tpl = null) {

		$app					= JFactory::getApplication();
		$this->t				= PhocacartUtils::setVars('user');
		$this->r				= new PhocacartRenderAdminview();
		$this->state			= $this->get('State');
		$this->form				= $this->get('Form');
		$this->formspecific		= $this->get('FormSpecific');
		$this->item				= $this->get('Item');
		$this->fields			= $this->get('Fields');


		if (isset($this->item->user_id) && (int)$this->item->user_id > 0) {
			$user_id		= $this->item->user_id;
		} else {
			$user_id		= $this->state->get($this->getName() . '.id');
		}

		$this->u			= JFactory::getUser($user_id);

		// There are two forms
		// 1) billing and shipping created by code
		// 2) other info created by XML (user_id, group)
		//$this->form->setValue('user_id', $user_id); // Add user_id to 2) so the field can get right Parameters



		$media = new PhocacartRenderAdminmedia();

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$bar 		= JToolbar::getInstance('toolbar');
		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$class		= ucfirst($this->t['tasks']).'Helper';
		$canDo		= $class::getActions($this->t, $this->state->get('filter.user_id'));

		$text = $isNew ? JText::_( $this->t['l'] . '_NEW' ) : JText::_($this->t['l'] . '_EDIT');
		JToolbarHelper::title(   JText::_( $this->t['l'] . '_CUSTOMER' ).': <small><small>[ ' . $text.' ]</small></small>' , 'user');

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit')){
			JToolbarHelper::apply($this->t['task'].'.apply', 'JTOOLBAR_APPLY');
			JToolbarHelper::save($this->t['task'].'.save', 'JTOOLBAR_SAVE');
			//JToolbarHelper::addNew($this->t['task'].'.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}

		if (empty($this->item->id))  {
			JToolbarHelper::cancel($this->t['task'].'.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolbarHelper::cancel($this->t['task'].'.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolbarHelper::divider();
		JToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}
}
?>
