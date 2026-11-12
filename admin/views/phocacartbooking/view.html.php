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

class PhocaCartCpViewPhocaCartBooking extends HtmlView
{
    protected $form;
    protected $item;
    protected $state;
    protected $t;
    protected $r;
    protected $p;
    protected $history;
    protected $orderInfo;

    public function display($tpl = null)
    {
        $this->t = PhocacartUtils::setVars('booking');
        $this->r = new PhocacartRenderAdminview();
        $this->p = PhocacartUtils::getComponentParameters();

        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        $model           = $this->getModel();
        $this->history   = $model->getHistory($this->item->id);
        $this->orderInfo = $model->getOrderInfo($this->item->id);

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
		ToolbarHelper::title(   Text::_( 'COM_PHOCACART_BOOKING' ).': <small><small>[ ' . $text.' ]</small></small>' , 'calendar');
        //ToolbarHelper::title($isNew ? Text::_('COM_PHOCACART_BOOKING_NEW') : Text::_('COM_PHOCACART_BOOKING_EDIT'), 'calendar-check');

        ToolbarHelper::apply('phocacartbooking.apply');
        ToolbarHelper::save('phocacartbooking.save');
        ToolbarHelper::save2new('phocacartbooking.save2new');
        ToolbarHelper::cancel('phocacartbooking.cancel', 'JTOOLBAR_CANCEL');
    }
}
