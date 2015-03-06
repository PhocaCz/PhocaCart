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

class PhocaCartCpViewPhocaCartUser extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;
	protected $fields;
	protected $t;
	protected $u;

	public function display($tpl = null) {
		
		$app			= JFactory::getApplication();
		$this->t		= PhocaCartUtils::setVars('user');
		$this->state	= $this->get('State');
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->fields	= $this->get('Fields');
		
		
		$this->u		= JFactory::getUser($this->item->user_id);
		
	
		JHTML::stylesheet( $this->t['s'] );

		$this->addToolbar();
		parent::display($tpl);	
	}
	
	protected function addToolbar() {
		
		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		JRequest::setVar('hidemainmenu', true);
		$bar 		= JToolBar::getInstance('toolbar');
		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$class		= ucfirst($this->t['tasks']).'Helper';
		$canDo		= $class::getActions($this->t, $this->state->get('filter.user_id'));
		
		$text = $isNew ? JText::_( $this->t['l'] . '_NEW' ) : JText::_($this->t['l'] . '_EDIT');
		JToolBarHelper::title(   JText::_( $this->t['l'] . '_USER' ).': <small><small>[ ' . $text.' ]</small></small>' , 'users');
		
		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit')){
			JToolBarHelper::apply($this->t['task'].'.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save($this->t['task'].'.save', 'JTOOLBAR_SAVE');
			//JToolBarHelper::addNew($this->t['task'].'.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}
	
		if (empty($this->item->id))  {
			JToolBarHelper::cancel($this->t['task'].'.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel($this->t['task'].'.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.'.$this->t['c'], true );
	}
}
?>
