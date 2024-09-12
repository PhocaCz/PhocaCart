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

trait FeaturedControllerTrait
{
    protected $featuredController = 'phocacartitems';
    protected $featuredAuthorise = 'phocacartitem';

    public function featured(): void
    {
        try {
            $input = Factory::getApplication()->input;
            $user   = Factory::getApplication()->getIdentity();
            $id    = $input->get('id', null, 'int');
            $state = $input->get('state', null, 'int');

            if (!$id || !in_array($state, [0, 1, 2, -2], true)) {
                throw new \Exception(Text::_('COM_PHOCACART_AJAX_REQUEST_ERROR'));
            }

            if (!$user->authorise('core.edit.state', 'com_phocacart.' . $this->featuredAuthorise . '.' . $id)) {
                throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
            }

            $model = $this->getModel();

            $model->featured($id, $state);
            $errors = $model->getErrors();
            if ($errors) {
                throw new \Exception($errors[0]);
            }

            if ($state === 1) {
                $message = $this->text_prefix . '_N_ITEMS_FEATURED';
                $newState = 0;
            } else {
                $message = $this->text_prefix . '_N_ITEMS_UNFEATURED';
                $newState = 1;
            }

            $result = [
                'phajax'  => 'state=' . $newState,
                'content' => HtmlGridHelper::featuredIcon($state, true, $this->featuredController),
            ];

            echo new JsonResponse($result, Text::plural($message, 1));
        }
        catch(\Exception $e)
        {
            echo new JsonResponse($e);
        }
    }
}
