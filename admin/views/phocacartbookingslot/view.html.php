<?php
/**
 * @package    phocacart
 * @subpackage Views
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class PhocaCartCpViewPhocaCartBookingSlot extends HtmlView
{
    protected $form;
    protected $item;
    protected $state;
    protected $t;
    protected $r;

    public function display($tpl = null)
    {
        $this->t = PhocacartUtils::setVars('bookingslot');
        $this->r = new PhocacartRenderAdminview();

        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }
        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew = ((int)$this->item->id === 0);

        $text = $isNew ? Text::_( 'COM_PHOCACART_NEW' ) : Text::_('COM_PHOCACART_EDIT');
		ToolbarHelper::title(   Text::_( 'COM_PHOCACART_BOOKING_SLOT' ).': <small><small>[ ' . $text.' ]</small></small>' , 'calendar');
        //ToolbarHelper::title($isNew ? Text::_('COM_PHOCACART_BOOKING_SLOT_NEW') : Text::_('COM_PHOCACART_BOOKING_SLOT_EDIT'), 'calendar');

        ToolbarHelper::apply('phocacartbookingslot.apply');
        ToolbarHelper::save('phocacartbookingslot.save');
        ToolbarHelper::save2new('phocacartbookingslot.save2new');
        ToolbarHelper::cancel('phocacartbookingslot.cancel', 'JTOOLBAR_CANCEL');
    }
}
