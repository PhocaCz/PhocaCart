<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die();

require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';

class PhocaCartCpControllerPhocacartWishlists extends PhocaCartCpControllerPhocaCartCommons
{
    public function &getModel($name = 'PhocacartWishlist', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

    public function sendwatchdog() {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $app	= Factory::getApplication();
        /** @var PhocaCartCpModelPhocacartWishlist $model */
        $model = $this->getModel();
        $model->sendWatchdogEmails();
        foreach ($model->getErrors() as $error) {
            $app->enqueueMessage($error, 'error');
        }
        $app->enqueueMessage(Text::plural('COM_PHOCACART_N_WATCHDOG_EMAIL_SENT', $model->getState('watchdog_count')), 'info');
        if ($model->getState('watchdog_repeat')) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_WATCHDOG_EMAIL_SENT_REPEAT'), 'warning');
        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_WATCHDOG_EMAIL_SENT_ALL'), 'success');
        }

        $this->setRedirect('index.php?option=com_phocacart&view=phocacartwishlists');
    }
}
