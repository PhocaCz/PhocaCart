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
	function display($tpl = null){

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}

		$app		= Factory::getApplication();
		$q			= $app->input->get( 'q', '', 'string'  );
		$id			= $app->input->get( 'item_id', '', 'int'  );

		if (isset($q) && $q != '') {
			$db		= Factory::getDbo();
			$query	= $db->getQuery(true);
			$path	= PhocacartPath::getPath('productimage');


			$columns	= 'a.id as id, a.title as title, a.image as image';
			$groupsFull	= 'a.id, a.title, a.image';
			$groupsFast	= 'a.id';
			$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

			$query->select($columns);
			$query->from('`#__phocacart_products` AS a');
			//$query->select('c.title AS category_title, c.id AS category_id');
			//$query->join('LEFT', '#__phocacart_categories AS c ON c.id = a.catid');


			$query->select('group_concat(CONCAT_WS(":", c.id, c.title) SEPARATOR \',\') AS categories');
			$query->select('group_concat(c.id SEPARATOR \',\') AS categories_id');
			$query->select('group_concat(c.title SEPARATOR \' \') AS categories_title');
			$query->join('LEFT', '#__phocacart_product_categories AS pc ON pc.product_id = a.id');
			$query->join('LEFT', '#__phocacart_categories AS c ON c.id = pc.category_id');

			$search = $db->Quote('%'.$db->escape($q, true).'%');
			if ((int)$id > 0) {
				$query->where('( a.id <> '.(int)$id.')');
			}
			$query->where('( a.title LIKE '.$search.')');
			$query->group($db->escape($groups));
			$query->order($db->escape('a.ordering'));

			$db->setQuery($query);

            try {
                $items 	= $db->loadObjectList();
            } catch (\RuntimeException $e) {
                $response = array(
                    'status' => '0',
                    'error' => '<span class="ph-result-txt ph-error-txt">Database Error - Getting Selected Products</span>');
                echo json_encode($response);
                return;
            }

			/*if (!$db->query()) {
				$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">Database Error - Getting Selected Products</span>');
				echo json_encode($response);
				return;
			}
			$items 	= $db->loadObjectList();*/

			$itemsA	= array();
			if (!empty($items)) {
				foreach ($items as $k => $v) {
					$itemsA[$k]['id'] 				= $v->id;
					$itemsA[$k]['title'] 			= $v->title . ' ('.$v->categories_title.')';
					$itemsA[$k]['categories'] 		= $v->categories;
					if ($v->image != '') {
						$thumb = PhocacartFileThumbnail::getOrCreateThumbnail($v->image, '', 0, 0, 0, 0, 'productimage');
						if ($thumb['thumb_name_s_no_rel'] != '') {
							$itemsA[$k]['image']= $thumb['thumb_name_s_no_rel'];
						}
					}
				}
			}

			$response = array(
			'status'	=> '1',
			'items'		=> $itemsA);
			echo json_encode($response);
			return;
		}

		$response = array(
		'status'	=> '1',
		'items'		=> array());
		echo json_encode($response);
		return;
	}
}
?>
