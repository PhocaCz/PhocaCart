<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');

class PhocaCartCpViewPhocaCartItemA extends JViewLegacy
{
	function display($tpl = null){
			
		if (!JRequest::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		
		$app		= JFactory::getApplication();
		$q			= $app->input->get( 'q', '', 'string'  );
		$id			= $app->input->get( 'item_id', '', 'int'  );
		
		if (isset($q) && $q != '') {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			$path	= PhocaCartPath::getPath('productimage');
			
			$query->select('a.id as id, a.title as title, a.image as image');
			$query->from('`#__phocacart_products` AS a');
			$query->select('c.title AS category_title, c.id AS category_id');
			$query->join('LEFT', '#__phocacart_categories AS c ON c.id = a.catid');
			$search = $db->Quote('%'.$db->escape($q, true).'%');
			if ((int)$id > 0) {
				$query->where('( a.id <> '.(int)$id.')');
			}
			$query->where('( a.title LIKE '.$search.')');
			$query->order($db->escape('a.ordering'));
			
			$db->setQuery($query);
			if (!$db->query()) {
				$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">Database Error - Getting Selected Products</span>');
				echo json_encode($response);
				return;
			}
			$items 	= $db->loadObjectList();
			$itemsA	= array();
			if (!empty($items)) {
				foreach ($items as $k => $v) {
					$itemsA[$k]['id'] 		= $v->id;
					$itemsA[$k]['title'] 	= $v->title . '('.$v->category_title.')';
					if ($v->image != '') {
						$thumb = PhocaCartFileThumbnail::getOrCreateThumbnail($v->image, '', 0, 0, 0, 0, 'productimage');
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