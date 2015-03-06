<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartAttribute
{
	public static function getAttributesById($productId) {
	
		$db = JFactory::getDBO();
		
		$query = 'SELECT a.id, a.title, a.required, a.type'
				.' FROM #__phocacart_attributes AS a'
			    .' WHERE a.product_id = '.(int) $productId;
		$db->setQuery($query);
		$attributes = $db->loadObjectList();
		return $attributes;
	}
	
	public static function getOptionsById($attributeId) {
	
		$db =JFactory::getDBO();
		
		$query = 'SELECT a.id, a.title, a.amount, a.operator, a.stock, a.operator_weight, a.weight';
		$query .= ' FROM #__phocacart_attribute_values AS a'
			    .' WHERE a.attribute_id = '.(int) $attributeId;
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}
	
	public static function getTypeArray() {
		$o = array('1' => JText::_('COM_PHOCACART_ATTR_TYPE_SELECT'));
		return $o;
	}
	
	public static function getRequiredArray() {
		$o = array('0' => JText::_('COM_PHOCACART_NO'), '1' => JText::_('COM_PHOCACART_YES'));
		return $o;
	}
	
	public static function getOperatorArray() {
		$o = array('+' => '+', '-' => '-');
		return $o;
	}
	
	public static function storeAttributesById($productId, $attributesArray) {
	
	
		if ((int)$productId > 0) {
			$db =JFactory::getDBO();
			
			// REMOVE OPTIONS
			// Get attribute ids which will be removed (to remove options)
			$query = ' SELECT id '
					.' FROM #__phocacart_attributes'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$deleteIds = $db->loadColumn();
			
			if (!empty($deleteIds)) {
				$deleteString = implode($deleteIds, ',');
			
				$query = ' DELETE '
					.' FROM #__phocacart_attribute_values'
					. ' WHERE attribute_id IN ('. (string)$deleteString.')';
				$db->setQuery($query);
				$db->execute();
			}
			
			// REMOVE ATTRIBUTES
			$query = ' DELETE '
					.' FROM #__phocacart_attributes'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();
			
			// ADD ATTRIBUTES
			if (!empty($attributesArray)) {
				
				
				
				
				foreach($attributesArray as $k => $v) {
					$valuesString 	= '';
					$valuesString 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.(int)$v['required'].', '.(int)$v['type'].')';
					$query = ' INSERT INTO #__phocacart_attributes (product_id, title, required, type)'
								.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute(); // insert is not done together but step by step because of getting last insert id
					
					// ADD OPTIONS
					$newId = $db->insertid();
					if (!empty($v['option']) && isset($newId) && (int)$newId > 0) {
						
						$options		= array();
						foreach($v['option'] as $k2 => $v2) {
						
							$options[] 	= '('.(int)$newId.', '.$db->quote($v2['title']).', '.$db->quote($v2['operator']).', '.$db->quote($v2['amount']).', '.(int)$v2['stock'].', '.$db->quote($v2['operatorweight']).', '.$db->quote($v2['weight']).')';
							if (!empty($options)) {
								$valuesString2 = implode($options, ',');
							}
						}
						$query = ' INSERT INTO #__phocacart_attribute_values (attribute_id, title, operator, amount, stock, operator_weight, weight)'
									.' VALUES '.(string)$valuesString2;
									
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
	}
	
	public static function getAttributesAndOptions($productId) {
		
		$attributes = array();
		$attributes = self::getAttributesById($productId);
		
		if (!empty($attributes)) {
			foreach($attributes as $k => $v) {
				$options = self::getOptionsById((int)$v->id);
				if (!empty($options)) {
					$attributes[$k]->options = $options;
				} else {
					$attributes[$k]->options = false;
				}	
			}
		}
		return $attributes;
	}
	
	public static function getAttributeValue($id, $attributeId) {
		$db =JFactory::getDBO();
		$query = ' SELECT a.id, a.title, a.amount, a.operator, a.weight, a.operator_weight, a.stock,'
		.' aa.id as aid, aa.title as atitle'
		.' FROM #__phocacart_attribute_values AS a'
		.' LEFT JOIN #__phocacart_attributes AS aa ON a.attribute_id = aa.id'
		.' WHERE a.id = '.(int)$id . ' AND a.attribute_id = '.(int)$attributeId
		.' LIMIT 1';
		$db->setQuery($query);
		$attrib = $db->loadObject();
		return $attrib;
	
	}
	
	/*
	 * Check if attribute is required or not
	 * This is checked when adding products to cart (normally, this should not happen, as html5 input form checking should do it)
	 * Adding products to cart - this is only security check
	 * Checking products before making order - this is only security check
	 * Standard user will not add empty attributes if required because html5 form checking will tell him
	 * This is really only for cases, someone will try to forge the form - server side checking
	 */
	public static function checkIfRequired($id, $value) {
	
		if ((int)$id > 0 && (int)$value > 0) {
			return true;// Attribute set and value set too - we don't have anything to check, as attribute value was selected
		}
	
		if ((int)$id > 0 && (int)$value == 0) {
			$db =JFactory::getDBO();
			$query = ' SELECT a.required'
			.' FROM #__phocacart_attributes AS a'
			.' WHERE a.id = '.(int)$id
			.' LIMIT 1';
			$db->setQuery($query);
			$attrib = $db->loadObject();
			if (isset($attrib->required) && $attrib->required == 0) {
				return true;
			} else {
				return false;// seems like attribute is required but not selected
			}
		}
		
		return false;
	}
	
	
	/* Check if the product includes some required attribute
	 * If yes, but users tries to add the product without attribute (forgery)
	 * just check it on server side
	 * BE AWARE - this test runs only in case when attributes are empty
	 * We don't check if attribute was selected or not or if is required or not
	 * We didn't get any attribute when ordering this product and we only check
	 * if the product includes some attribute
	 */
	public static function checkIfExistsAndRequired($productId) {
		
		$wheres		= array();
		$wheres[] 	= ' a.id = '.(int)$productId;
		$db 		= JFactory::getDBO();
		$query = ' SELECT a.id,'
		.' at.required AS attribute_required'
		.' FROM #__phocacart_products AS a'
		.' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1'
		. ' WHERE ' . implode( ' AND ', $wheres )
		. ' LIMIT 1';
		$db->setQuery($query);
		$attrib = $db->loadObject();
		
		if ((int)$attrib->attribute_required > 0) {
			return false;
		} else {
			return true;
		}
		
		return false;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	


	

	
/*
	public static function storeOptionsByAttributeId($attributeId, $optArray) {
	
		if ((int)$attributeId > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_attribute_values'
					. ' WHERE attribute_id = '. (int)$attributeId;
			$db->setQuery($query);
		
			
			if (!empty($optArray)) {
				
				$values 		= array();
				$valuesString 	= '';
				
				foreach($optArray as $k => $v) {
					if (isset($v['title']) && $v['title'] != ''  && isset($v['amount']) && isset($v['operator'])) {
						$values[] = ' ('.(int)$attributeId.', \''.$v['title'].'\', \''.$v['operator'].'\', \''.(float)$v['amount'].'\')';
					}
				}
			
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO #__phocacart_attribute_values (attribute_id, title, operator, amount)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
				
				}
			}
		}
	}
	
	
	
	public static function getAllAttributesSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {
	
		$db = JFactory::getDBO();
		$query = 'SELECT a.id AS value, CONCAT(a.title_attribute,\' (\', a.title,  \')\') AS text'
				.' FROM #__phocacart_attributes AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);
		
		
		
		$attributes = $db->loadObjectList();
		
		$attributesO = JHTML::_('select.genericlist', $attributes, $name, 'class="inputbox" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);
		
		return $attributesO;
	}
	
	*/
}
?>