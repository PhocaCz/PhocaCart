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

class PhocaCartCpViewPhocacartCategory extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;
	protected $t;

	public function display($tpl = null) {
	
		$this->t		= PhocacartUtils::setVars('category');
		
		$this->state	= $this->get('State');
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$user 			= JFactory::getUser();
		$model			= $this->getModel();

		JHtml::_('behavior.calendar');
		$media = new PhocacartRenderAdminmedia();
		
		//Data from model
		//$this->item	=& $this->get('Data');

		$lists 	= array();		
		$isNew	= ((int)$this->item->id == 0);

		// Edit or Create?
		if (!$isNew) {
			$model->checkout( $user->get('id') );
		} else {
			// Initialise new record
			$this->item->published 		= 1;
			$this->item->order 			= 0;
			$this->item->access			= 0;
		}
		
		$url = 'index.php?option=com_phocacart&view=phocacartthumba&format=json&tmpl=component&'. JSession::getFormToken().'=1';
		PhocacartRenderJs::renderAjaxDoRequest(JText::_('COM_PHOCACART_CHECKING_IMAGE_THUMBNAIL_PLEASE_WAIT'));
		PhocacartRenderJs::renderAjaxDoRequestAfterChange($url, 'categoryimage', 'imageCreateThumbs');
		PhocacartRenderJs::renderAjaxDoRequestAfterPaste($url, 'categoryimage');

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
		$canDo		= $class::getActions($this->t, $this->state->get('filter.category_id'));

		$text = $isNew ? JText::_( $this->t['l'].'_NEW' ) : JText::_($this->t['l'].'_EDIT');
		JToolbarHelper::title(   JText::_( $this->t['l'].'_CATEGORY' ).': <small><small>[ ' . $text.' ]</small></small>' , 'folder-open');

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit')){
			JToolbarHelper::apply($this->t['task'].'.apply', 'JTOOLBAR_APPLY');
			JToolbarHelper::save($this->t['task'].'.save', 'JTOOLBAR_SAVE');
			JToolbarHelper::addNew($this->t['task'].'.save2new', 'JTOOLBAR_SAVE_AND_NEW');
			
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			//JToolbarHelper::custom($this->t['c'].'cat.save2copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		if (empty($this->item->id))  {
			JToolbarHelper::cancel($this->t['task'].'.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolbarHelper::cancel($this->t['task'].'.cancel', 'JTOOLBAR_CLOSE');
		}
		
		
	
		
		JToolbarHelper::divider();
		JToolbarHelper::help( 'screen.'.$this->t['c'], true );
		
		PhocacartRenderAdminview::renderWizardButton('back');
	}
}
?>
