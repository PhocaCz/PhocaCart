<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
jimport( 'joomla.application.component.view');

class PhocaCartCpViewPhocaCartItemA extends HtmlView
{
	function display($tpl = null)
    {
		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}

		$app		= Factory::getApplication();
		$search		= $app->input->get('q', '', 'string');
		$id			= $app->input->get('item_id', '', 'int');

        $search = trim($search);

		if ($search) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $columns    = 'a.id, a.title, a.sku, a.ean, a.image';
            $groupsFull = 'a.id, a.title, a.sku, a.ean, a.image';
            $groupsFast = 'a.id';
            $groups     = PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

            $query->select($columns);
            $query->from('`#__phocacart_products` AS a');


            $query->select('group_concat(CONCAT_WS(":", c.id, c.title) SEPARATOR \',\') AS categories');
            $query->select('group_concat(c.id SEPARATOR \',\') AS categories_id');
            $query->select('group_concat(c.title SEPARATOR \', \') AS categories_title');
            $query->join('LEFT', '#__phocacart_product_categories AS pc ON pc.product_id = a.id');
            $query->join('LEFT', '#__phocacart_categories AS c ON c.id = pc.category_id');

            if ($id) {
                $query->where('a.id <> ' . $id);
            }

            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $words = explode(' ', $search);
                $words = array_filter($words);

                $searchMatchingOption = PhocacartUtils::getComponentParameters()->get('search_matching_option_admin', 'exact');

                switch ($searchMatchingOption) {
                    case 'all':
                    case 'any':
                        $wheres = array();
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
                        $wheresSub   = array();
                        $wheresSub[] = 'a.title LIKE ' . $text;
                        $wheresSub[] = 'a.alias LIKE ' . $text;
                        $wheresSub[] = 'a.sku LIKE ' . $text;
                        $wheresSub[] = 'a.ean LIKE ' . $text;
                        $wheresSub[] = 'exists (select ps.id from #__phocacart_product_stock AS ps WHERE a.id = ps.product_id AND ps.sku LIKE ' . $text . ' OR ps.ean LIKE ' . $text . ') ';
                        $query->where('((' . implode(') OR (', $wheresSub) . '))');

                        break;
                }
            }

            $query->group($groups);
            $query->order('a.ordering');
            $query->setLimit(10);

            $db->setQuery($query);

            try {
                $items = $db->loadObjectList();
            }
            catch (\RuntimeException $e) {
                $response = array(
                    'status' => '0',
                    'error'  => '<span class="ph-result-txt ph-error-txt">Database Error - Getting Selected Products</span>');
                echo json_encode($response);

                return;
            }

            $itemsA = array();
            if (!empty($items)) {
                foreach ($items as $k => $v) {
                    $itemsA[$k]['id']         = $v->id;
                    $itemsA[$k]['title']      = $v->title;
                    $itemsA[$k]['categories_title'] = $v->categories_title;
                    $itemsA[$k]['categories'] = $v->categories;
                    $itemsA[$k]['sku']        = $v->sku;
                    $itemsA[$k]['ean']        = $v->ean;
                    $itemsA[$k]['image']      = null;
                    if ($v->image != '') {
                        $thumb = PhocacartFileThumbnail::getOrCreateThumbnail($v->image, '', 0, 0, 0, 0, 'productimage');
                        if ($thumb['thumb_name_s_no_rel'] != '') {
                            $itemsA[$k]['image'] = $thumb['thumb_name_s_no_rel'];
                        }
                    }
                }
            }

            $response = [
                'status' => '1',
                'items'  => $itemsA
            ];
            echo json_encode($response);

            return;
        }

		$response = [
		    'status'	=> '1',
		    'items'		=> []
        ];
		echo json_encode($response);
	}
}

