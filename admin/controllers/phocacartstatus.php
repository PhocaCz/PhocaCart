<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Phoca\PhocaCart\Mail\MailHelper;

defined('_JEXEC') or die();
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';

class PhocaCartCpControllerPhocaCartStatus extends PhocaCartCpControllerPhocaCartCommon
{
    protected $view_list = 'phocacartstatuses';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?CMSWebApplicationInterface $app = null, ?Input $input = null, ?FormFactoryInterface $formFactory = null)
    {
        parent::__construct($config, $factory, $app, $input, $formFactory);
        $this->registerTask('mailtemplate', 'editMailtemplate');
    }

    public function editMailtemplate()
    {
        $statusId = $this->input->getInt('id');
        if (!$statusId) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
            $this->app->redirect(Route::_('index.php?option=com_mails&view=templates', false));
            return;
        }

        switch ($this->input->getCmd('template_id')) {
            case 'status': $templateId = 'com_phocacart.order_status.' . $statusId; break;
            case 'notification': $templateId = 'com_phocacart.order_status.notification.' . $statusId; break;
            case 'gift': $templateId = 'com_phocacart.order_status.gift.' . $statusId; break;
            case 'gift_notification': $templateId = 'com_phocacart.order_status.gift_notification.' . $statusId; break;
            default: $templateId = null;
        }

        if (!$templateId) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
            $this->app->redirect(Route::_('index.php?option=com_mails&view=templates', false));
            return;
        }

        $language = $this->app->getInput()->getCmd('language');
        if (!$language) {
            $language = ComponentHelper::getParams('com_languages')->get('site');
        }

        MailHelper::checkOrderStatusMailTemplate($statusId, $this->input->getCmd('template_id'));

        $this->app->redirect(Route::_('index.php?option=com_mails&task=template.edit&template_id=' . $templateId . '&language=' . $language, false));
    }
}
