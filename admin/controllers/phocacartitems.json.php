<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Database\DatabaseInterface;
use Phoca\PhocaCart\Html\Grid\HtmlGridHelper;

require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';

class PhocaCartCpControllerPhocaCartItems extends PhocaCartCpControllerPhocaCartCommons
{
    public function &getModel($name = 'PhocaCartItem', $prefix = 'PhocaCartCpModel', $config = array())
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

    public function state(): void
    {
        try {
            $input = Factory::getApplication()->input;
            $id    = $input->get('id', null, 'int');
            $state = $input->get('state', null, 'int');

            if (!$id || !in_array($state, [0, 1, 2, -2], true)) {
                throw new Exception(Text::_('COM_PHOCACART_AJAX_REQUEST_ERROR'));
            }

            $model = $this->getModel();

            $model->publish($id, $state);
            $errors = $model->getErrors();
            if ($errors) {
                throw new Exception($errors[0]);
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
        catch(Exception $e)
        {
            echo new JsonResponse($e);
        }
    }

    public function featured(): void
    {
        try {
            $input = Factory::getApplication()->input;
            $user   = Factory::getApplication()->getIdentity();
            $id    = $input->get('id', null, 'int');
            $state = $input->get('state', null, 'int');

            if (!$id || !in_array($state, [0, 1, 2, -2], true)) {
                throw new Exception(Text::_('COM_PHOCACART_AJAX_REQUEST_ERROR'));
            }

            if (!$user->authorise('core.edit.state', 'com_phocacart.phocacartitem.' . $id)) {
                throw new Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
            }

            $model = $this->getModel();

            $model->featured($id, $state);
            $errors = $model->getErrors();
            if ($errors) {
                throw new Exception($errors[0]);
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
                'content' => HtmlGridHelper::featuredIcon($state),
            ];

            echo new JsonResponse($result, Text::plural($message, 1));
        }
        catch(Exception $e)
        {
            echo new JsonResponse($e);
        }
    }

    public function search()
    {
        $search = $this->input->get('search', '', 'string');
        $id     = $this->input->get('productId', '', 'int');

        $search = trim($search);

        if (!$search) {
            echo json_encode([]);
            $this->app->close();
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select('DISTINCT a.id AS value, a.title AS text');
        $query->from('`#__phocacart_products` AS a');

        if ($id) {
            $query->where('a.id <> ' . $id);
        }

        $words = explode(' ', $search);
        $words = array_filter($words);

        $searchMatchingOption = PhocacartUtils::getComponentParameters()->get('search_matching_option_admin', 'exact');

        switch ($searchMatchingOption) {
            case 'all':
            case 'any':
                $wheres = [];
                foreach ($words as $word) {
                    $word        = $db->quote('%' . $db->escape($word, true) . '%', false);
                    $wheresSub   = array();
                    $wheresSub[] = 'a.title LIKE ' . $word;
                    $wheresSub[] = 'a.alias LIKE ' . $word;
                    $wheresSub[] = 'a.sku LIKE ' . $word;
                    $wheresSub[] = 'a.ean LIKE ' . $word;
                    $wheresSub[] = 'exists (select ps.id from #__phocacart_product_stock AS ps WHERE a.id = ps.product_id AND ps.sku LIKE ' . $word . ' OR ps.ean LIKE ' . $word . ') ';
                    $wheres[]    = implode(' OR ', $wheresSub);
                }

                $query->where('((' . implode(($searchMatchingOption == 'all' ? ') AND (' : ') OR ('), $wheres) . '))');
                break;

            case 'exact':
            default:
                $text        = $db->quote('%' . $db->escape(implode(' ', $words), true) . '%', false);
                $wheresSub   = [];
                $wheresSub[] = 'a.title LIKE ' . $text;
                $wheresSub[] = 'a.alias LIKE ' . $text;
                $wheresSub[] = 'a.sku LIKE ' . $text;
                $wheresSub[] = 'a.ean LIKE ' . $text;
                $wheresSub[] = 'exists (select ps.id from #__phocacart_product_stock AS ps WHERE a.id = ps.product_id AND ps.sku LIKE ' . $text . ' OR ps.ean LIKE ' . $text . ') ';
                $query->where('((' . implode(') OR (', $wheresSub) . '))');

                break;
        }

        $query->order('a.ordering');
        $query->setLimit(10);

        $db->setQuery($query);

        try {
            $items = $db->loadObjectList();
        }
        catch (\RuntimeException $e) {
        }

        // TODO HTML
        echo json_encode($items);
        $this->app->close();
    }
}

