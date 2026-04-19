<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die();

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;

/**
 * Controller for the EU Right of Withdrawal (cancellation) view.
 *
 * Handles the form POST via the 'cancel' task.
 *
 * @since  6.1.0
 */
class PhocaCartControllerCancellation extends BaseController
{
    /**
     * Process the withdrawal form submission.
     *
     * Validates the CSRF token, then delegates all business logic
     * (eligibility re-check, DB update, e-mail) to the model.
     * Redirects back to the orders list on both success and failure.
     *
     * @return  void
     *
     * @since  6.1.0
     */
    public function cancel(): void
    {
        // CSRF validation
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $app   = Factory::getApplication();
        $input = $app->getInput();

        $orderId  = (int) $input->post->get('order_id', 0, 'int');
        $token    = (string) $input->post->get('o', '', 'string');
        $confirm  = (int) $input->post->get('cancellation_confirm', 0, 'int');

        // Require checkbox confirmation
        if (!$confirm) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_CANCELLATION_ERROR_CONFIRM'), 'warning');
            $this->setRedirect(PhocacartRoute::getCancellationRoute($orderId, $token));
            return;
        }

        /** @var PhocaCartModelCancellation $model */
        $model  = $this->getModel('Cancellation', 'PhocaCartModel');
        $result = $model->cancel($orderId, $token);

        if ($result['success']) {
            $app->enqueueMessage($result['message']);
            // Redirect to orders list
            $ordersUrl = PhocacartRoute::getOrdersRoute();
            if ($token !== '') {
                $ordersUrl .= '&o=' . rawurlencode($token);
            }
            $this->setRedirect($ordersUrl);
        } else {
            $app->enqueueMessage($result['message'], 'warning');
            $this->setRedirect(PhocacartRoute::getCancellationRoute($orderId, $token));
        }
    }
}
