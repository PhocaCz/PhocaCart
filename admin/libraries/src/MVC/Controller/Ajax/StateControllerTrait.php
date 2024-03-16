<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\MVC\Controller\Ajax;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Phoca\PhocaCart\Html\Grid\HtmlGridHelper;

trait StateControllerTrait
{
    public function state(): void
    {
        try {
            $input = Factory::getApplication()->input;
            $id    = $input->get('id', null, 'int');
            $state = $input->get('state', null, 'int');

            if (!$id || !in_array($state, [0, 1, 2, -2], true)) {
                throw new \Exception(Text::_('COM_PHOCACART_AJAX_REQUEST_ERROR'));
            }

            $model = $this->getModel();

            $model->publish($id, $state);
            $errors = $model->getErrors();
            if ($errors) {
                throw new \Exception($errors[0]);
            }

            if ($state === 1) {
                $message = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                $newState = 0;
            } elseif ($state === 0) {
                $message = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                $newState = 1;
            } elseif ($state === 2) {
                $message = $this->text_prefix . '_N_ITEMS_ARCHIVED';
                $newState = 0;
            } else {
                $message = $this->text_prefix . '_N_ITEMS_TRASHED';
                $newState = 1;
            }

            $result = [
                'phajax'  => 'state=' . $newState,
                'content' => HtmlGridHelper::stateIcon($state),
            ];

            echo new JsonResponse($result, Text::plural($message, 1));
        }
        catch(\Exception $e)
        {
            echo new JsonResponse($e);
        }
    }

}
