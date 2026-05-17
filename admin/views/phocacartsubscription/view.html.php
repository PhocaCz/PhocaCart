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

class PhocaCartCpViewPhocaCartSubscription extends HtmlView
{
    protected $form;
    protected $item;
    protected $state;
    protected $t;
	protected $r;
	protected $p;
	protected $s;

    public function display($tpl = null)
    {

        $this->t		= PhocacartUtils::setVars('subscription');
		$this->r		= new PhocacartRenderAdminview();
        $this->p 		= PhocacartUtils::getComponentParameters();
		$this->s        = PhocacartRenderStyle::getStyles();

        $this->form          = $this->get('Form');
        $this->item          = $this->get('Item');
        $this->state         = $this->get('State');

        $model = $this->getModel();
        $this->history       = $model->getHistory($this->item->id);
        $this->acl           = $model->getACL($this->item->id);
        $this->allStatuses   = \PhocacartSubscription::getStatuses();
        $this->orderInfo     = $model->getOrderInfo($this->item->id);

        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        ToolbarHelper::title($isNew ? Text::_('COM_PHOCACART_SUBSCRIPTION_NEW') : Text::_('COM_PHOCACART_SUBSCRIPTION_EDIT'), 'card-checklist');

        ToolbarHelper::apply('phocacartsubscription.apply');
        ToolbarHelper::save('phocacartsubscription.save');
        ToolbarHelper::save2new('phocacartsubscription.save2new');
        ToolbarHelper::cancel('phocacartsubscription.cancel', 'JTOOLBAR_CANCEL');
    }
}
