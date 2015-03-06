<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartCompare
{
	protected $items     		= array();

	public function __construct() {
		$session 		= JFactory::getSession();
		$app 			= JFactory::getApplication();
		$this->items	= $session->get('compare', array(), 'phocaCart');
	}
	
	public function addItem($id = 0) {
		if ($id > 0) {
			$app 			= JFactory::getApplication();
			
			$count = count($this->items);
			
			if ($count > 2) {
				$message = JText::_('COM_PHOCACART_ONLY_THREE_PRODUCTS_CAN_BE_LISTED_IN_COMPARISON_LIST');
				$app->enqueueMessage($message, 'error');
				return false;
			}
			
			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {
				
				$message = JText::_('COM_PHOCACART_PRODUCT_INCLUDED_IN_COMPARISON_LIST');
				$app->enqueueMessage($message, 'error');
				return false;
			} else {
				$this->items[$id] = $id;
				$session 		= JFactory::getSession();
				$session->set('compare', $this->items, 'phocaCart');
			}
			return true;
		}
		return false;
	}
	
	public function removeItem($id = 0) {
		if ($id > 0) {
			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {
				unset($this->items[$id]);
				$session 		= JFactory::getSession();
				$session->set('compare', $this->items, 'phocaCart');
				return true;
			} else {
				return false;
			}
			return false;
		}
		return false;
	}
	
	public function emptyCompare() {
		$session 		= JFactory::getSession();
		$session->set('compare', array(), 'phocaCart');
	}
	
	public function getItems() {
		return $this->items;
	}
	
	public function renderList() {
		$db = JFactory::getDBO();
		$items = '';
		if (!empty($this->items)) {
			$items = implode (',', $this->items);
		} else {
			return false;
		}
		
		$where[]	= 'a.id IN ('.(string)$items.')';
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		$query = 'SELECT a.id, a.title'
				.' FROM #__phocacart_products AS a'
			    . $where;
		$db->setQuery($query);

		$compare 		= $db->loadObjectList();
		$uri 			= JFactory::getURI();
		$action			= $uri->toString();
		$actionBase64	= base64_encode($action);
		$linkComparison	= JRoute::_(PhocaCartRoute::getComparisonRoute());
	
		if (!empty($compare)) {
			foreach ($compare as $k => $v) {
				echo '<div class="row">';
				echo '<div class="col-sm-8 col-md-8">' . $v->title . '</div>';
				
				echo '<div class="col-sm-4 col-md-4">';
				
				echo '<form action="'.$linkComparison.'" method="post" id="phCompareRemove'.(int)$v->id.'">';
				echo '<input type="hidden" name="id" value="'.(int)$v->id.'">';
				echo '<input type="hidden" name="task" value="comparison.remove">';
				echo '<input type="hidden" name="tmpl" value="component" />';
				echo '<input type="hidden" name="option" value="com_phocacart" />';
				echo '<input type="hidden" name="return" value="'.$actionBase64.'" />';
				echo '<div class="pull-right">';
				echo '<div class="ph-category-item-compare"><a href="javascript::void();" onclick="document.getElementById(\'phCompareRemove'.(int)$v->id.'\').submit();" title="'.JText::_('COM_PHOCACART_COMPARE').'"><span class="glyphicon glyphicon-remove"></span></a></div>';
				echo '</div>';
				echo JHtml::_('form.token');
				echo '</form>';
				
				echo '</div>';
				
				echo '</div>';
			
			}
		}
		$linkCompare = JRoute::_(PhocaCartRoute::getComparisonRoute());
		echo '<div class="ph-small ph-right ph-u ph-cart-link-checkout"><a href="'.$linkCompare.'">'.JText::_('COM_PHOCACART_VIEW_COMPARISON_LIST').'</a></div>';
	}
	
	public function getFullItems() {
		$db = JFactory::getDBO();
		$items = '';
		if (!empty($this->items)) {
			$items = implode (',', $this->items);
		} else {
			return false;
		}

		$where[]	= 'a.id IN ('.(string)$items.')';
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		$query = 'SELECT a.id, a.title, a.description, a.price, a.image,'
				.' a.length, a.width, a.height, a.weight, a.volume,'
				.' a.stock, a.min_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability,'
				.' m.title as manufacturer_title'
				.' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_manufacturers AS m ON a.manufacturer_id = m.id'
			    . $where;
		$db->setQuery($query);
		$products = $db->loadAssocList();
		return $products;
	
	}
}
?>