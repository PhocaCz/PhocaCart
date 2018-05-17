<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.modeladmin');
use Joomla\String\StringHelper;

class PhocaCartCpModelPhocaCartItem extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	public $typeAlias 			= 'com_phocacart.phocacartitem';
	
	protected function canDelete($record){
		$user = JFactory::getUser();

		if (!empty($record->catid)) {
			// catid not used
			return $user->authorise('core.delete', 'com_phocacart.phocacartitem.'.(int) $record->catid);
		} else {
			return parent::canDelete($record);
		}
	}
	
	protected function canEditState($record) {
		$user = JFactory::getUser();

		if (!empty($record->catid)) {
			// catid not used
			return $user->authorise('core.edit.state', 'com_phocacart.phocacartitem.'.(int) $record->catid);
		} else {
			return parent::canEditState($record);
		}
	}
	
	public function getTable($type = 'PhocaCartItem', $prefix = 'Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) {
		
		$app	= JFactory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartitem', 'phocacartitem', array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_phocacart.edit.phocacart.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}
	
	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();
			
			// Make the numbers more readable
			// it has no influence on saving it to db
			$item->price 			= PhocacartPrice::cleanPrice($item->price);
			$item->price_original 	= PhocacartPrice::cleanPrice($item->price_original);
			$item->length 			= PhocacartPrice::cleanPrice($item->length);
			$item->width 			= PhocacartPrice::cleanPrice($item->width);
			$item->height 			= PhocacartPrice::cleanPrice($item->height);
			$item->weight 			= PhocacartPrice::cleanPrice($item->weight);
			$item->volume			= PhocacartPrice::cleanPrice($item->volume);
			$item->unit_amount 		= PhocacartPrice::cleanPrice($item->unit_amount);

		}

		return $item;
	}
	
	protected function prepareTable($table) {
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JApplicationHelper::stringURLSafe($table->alias);
		

		
		$table->price 			= PhocacartUtils::replaceCommaWithPoint($table->price);
		$table->price_original 	= PhocacartUtils::replaceCommaWithPoint($table->price_original);
		$table->length 			= PhocacartUtils::replaceCommaWithPoint($table->length);
		$table->width 			= PhocacartUtils::replaceCommaWithPoint($table->width);
		$table->height 			= PhocacartUtils::replaceCommaWithPoint($table->height);
		$table->weight 			= PhocacartUtils::replaceCommaWithPoint($table->weight);
		$table->volume 			= PhocacartUtils::replaceCommaWithPoint($table->volume);
		

		$table->tax_id 					= PhocacartUtils::getIntFromString($table->tax_id);
		$table->manufacturer_id			= PhocacartUtils::getIntFromString($table->manufacturer_id);
		$table->stock					= PhocacartUtils::getIntFromString($table->stock);
		$table->min_quantity			= PhocacartUtils::getIntFromString($table->min_quantity);
		$table->min_multiple_quantity	= PhocacartUtils::getIntFromString($table->min_multiple_quantity);
		$table->download_hits			= PhocacartUtils::getIntFromString($table->download_hits);
		$table->points_received			= PhocacartUtils::getIntFromString($table->points_received);
		$table->points_needed			= PhocacartUtils::getIntFromString($table->points_needed);

		if (empty($table->alias)) {
			$table->alias = JApplicationHelper::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			// WE HAVE SPECIFIC ORDERING
			/*if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				//$db->setQuery('SELECT MAX(ordering) FROM #__phocadownload');
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_productcategories WHERE category_id = '.(int)$table->category_id);
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}*/
		}
		else {
			// Set the values
			//$table->modified	= $date->toSql();
			//$table->modified_by	= $user->get('id');
		}
	}

	
	function save($data) {
		
		/*if ($data['alias'] == '') {
			$data['alias'] = $data['title'];
		}*/
		$app		= JFactory::getApplication();
		$input  	= JFactory::getApplication()->input;
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;
		
		
		// ALIAS
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0)) {
			if ($data['alias'] == null) {
				if (JFactory::getConfig()->get('unicodeslugs') == 1) {
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['title']);
				} else {
					$data['alias'] = JFilterOutput::stringURLSafe($data['title']);
				}


				if ($table->load(array('alias' => $data['alias']))){
					$msg = JText::_('COM_PHOCACART_SAVE_WARNING');
				}

				list($title, $alias) = $this->generateNewTitle(0, $data['alias'], $data['title']);
				$data['alias'] = $alias;

				if (isset($msg)) {
					JFactory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		} else if ($table->load(array('alias' => $data['alias'])) && ($table->id != $data['id'] || $data['id'] == 0)) {
			$this->setError(\JText::_('COM_PHOCACART_ERROR_ITEM_UNIQUE_ALIAS'));
			return false;
		}
		

	

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		// Load the row if saving an existing record.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		if(intval($table->date) == 0) {
			$table->date = JFactory::getDate()->toSql();
		}

		// if new item, order last in appropriate group
		// Not used in multiple mode
		//if (!$table->id) {
		//	$where = 'catid = ' . (int) $table->catid ;
		//	$table->ordering = $table->getNextOrder( $where );
		//}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onContentBeforeSave event.
		/* $result = $dispatcher->trigger($this->event_before_save, array($this->option.'.'.$this->name, $table, $isNew));
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		} */
		
		

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}
		

		
		
		// Test Thumbnails (Create if not exists)
		if ($table->image != '') {
			$thumb = PhocacartFileThumbnail::getOrCreateThumbnail($table->image, '', 1, 1, 1, 0, 'productimage');
		}
						
		if ((int)$table->id > 0) {
			
			if (!isset($data['catid_multiple'])) {
				$data['catid_multiple'] = array();
			}
			PhocacartCategoryMultiple::storeCategories($data['catid_multiple'], (int)$table->id);
			
			if (isset($data['featured'])) {
				$this->featured((int)$table->id, $data['featured']);
			}
		
			$dataRelated = '';
			if (!isset($data['related'])) {
				$dataRelated = '';
			} else {
				$dataRelated = $data['related'];
				if (isset($data['related'][0])) {
					$dataRelated = $data['related'][0];
				}
			}
			
			PhocacartRelated::storeRelatedItemsById($dataRelated, (int)$table->id );
			
			if (!isset($data['attributes'])) {
				$data['attributes'] = array();
			}
			if (!isset($data['group'])) {
				$data['group'] = array();
			}
			
				
			$pFormImg = $app->input->post->get('pformimg', array(), 'array');
			PhocacartImageAdditional::storeImagesByProductId((int)$table->id, $pFormImg);
			$pFormAttr = $app->input->post->get('pformattr', array(), 'array');
			PhocacartAttribute::storeAttributesById((int)$table->id, $pFormAttr);
			$pFormSpec = $app->input->post->get('pformspec', array(), 'array');
			PhocacartSpecification::storeSpecificationsById((int)$table->id, $pFormSpec);
			$pFormDisc = $app->input->post->get('pformdisc', array(), 'array');
			PhocacartDiscountProduct::storeDiscountsById((int)$table->id, $pFormDisc);
			
			PhocacartGroup::storeGroupsById((int)$table->id, 3, $data['group']);
			
	
			PhocacartPriceHistory::storePriceHistoryById((int)$table->id, $data['price']);
			
			
			PhocacartGroup::updateGroupProductPriceById((int)$table->id, $data['price']);
			PhocacartGroup::updateGroupProductRewardPointsById((int)$table->id, $data['points_received']);
			
			
			// UPDATE this file too:
			// administrator\components\com_phocacart\libraries\phocacart\product\product.php storeProduct() function
			
			if (!isset($data['tags'])) {
				$data['tags'] = array();
			}
			
			PhocacartTag::storeTags($data['tags'], (int)$table->id);

		}

		// Clean the cache.
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		// Trigger the onContentAfterSave event.
		//$dispatcher->trigger($this->event_after_save, array($this->option.'.'.$this->name, $table, $isNew));

		$pkName = $table->getKeyName();
		if (isset($table->$pkName)) {
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		return true;
	}
	
		

	public function delete(&$cid = array()) {
		
		
		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			
			$table = $this->getTable();
			if (!$this->canDelete($table)){
				$error = $this->getError();
				if ($error){
					JLog::add($error, JLog::WARNING);
					return false;
				} else {
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING);
					return false;
				}
			}
			
			// 1. DELETE ITEMS
			$query = 'DELETE FROM #__phocacart_products'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 2. DELETE ATTRIBUTE OPTIONS
			$query = 'SELECT id FROM #__phocacart_attributes WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery($query);
			$attrOptions = $this->_db->loadAssocList();
			$attrArray = array();
			if (!empty($attrOptions)) {
				foreach($attrOptions as $k => $v) {
					$attrArray[] = $v['id'];				
				}
				if (!empty($attrArray)) {
					$attrs = implode( ',', $attrArray );
					$query = 'DELETE FROM #__phocacart_attribute_values'
							. ' WHERE attribute_id IN ( '.$attrs.' )';
					$this->_db->setQuery( $query );
					$this->_db->execute();
				}
			}
			
			// 3. DELETE ATTRIBUTES
			$query = 'DELETE FROM #__phocacart_attributes'
					. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			
			// 4. DELETE RELATED
			$query = 'DELETE FROM #__phocacart_product_related'
				. ' WHERE product_a IN ( '.$cids.' ) OR product_b IN ('.$cids.')';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 5. DELETE FEATURED
			$query = 'DELETE FROM #__phocacart_product_featured'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			$tableF = $this->getTable('PhocacartFeatured', 'Table');
			$tableF->reorder();
			
			// 6. DELETE IMAGES
			$query = 'DELETE FROM #__phocacart_product_images'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 7. DELETE REVIEWS
			$query = 'DELETE FROM #__phocacart_reviews'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 8. DELETE CATEGORY RELATIONSHIP
			$query = 'DELETE FROM #__phocacart_product_categories'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 9. DELETE SPECIFICATIONS
			$query = 'DELETE FROM #__phocacart_specifications'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 9. DELETE PRODUCT DISCOUNTS
			$query = 'DELETE FROM #__phocacart_product_discounts'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 9. DELETE PRODUCT CUSTOMER GROUPS
			$query = 'DELETE FROM #__phocacart_item_groups'
				. ' WHERE item_id IN ( '.$cids.' )'
				. ' AND type = 3';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 10. DELETE PRODUCT PRICE CUSTOMER GROUPS
			$query = 'DELETE FROM #__phocacart_product_price_groups'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			
			// 11. DELETE PRODUCT POINT CUSTOMER GROUPS
			$query = 'DELETE FROM #__phocacart_product_point_groups'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
		}
		return true;
	}

	protected function batchCopy($value, $pks, $contexts)
	{
		
		// Destination Category
		$categoryId	= (int) $value;
		// Source Category (current category)
		$app 			= JFactory::getApplication('administrator');
		$currentCatid 	= $app->input->post->get('filter_category_id', 0, 'int');
		
		$table	= $this->getTable();
		$db		= $this->getDbo();

		// Check that the category exists
		if ($categoryId) {
			$categoryTable = JTable::getInstance('PhocacartCategory', 'Table');
			if (!$categoryTable->load($categoryId)) {
				if ($error = $categoryTable->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
					return false;
				}
			}
		}

		if (empty($categoryId)) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
			return false;
		}

		// Check that the user has create permission for the component
		$extension	= JFactory::getApplication()->input->get('option');
		$user		= JFactory::getUser();
		if (!$user->authorise('core.create', $extension)) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
			return false;
		}
		
		//$i		= 0;

		// Parent exists so we let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$table->reset();

			// Check that the row actually exists
			if (!$table->load($pk)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$data = $this->generateNewTitle($categoryId, $table->alias, $table->title);
			$table->title   = $data['0'];
			$table->alias   = $data['1'];

			// Reset the ID because we are making a copy
			$table->id		= 0;

			// New category ID
		//	$table->catid	= $categoryId;
			
			// Ordering
		//	$table->ordering = $this->increaseOrdering($categoryId);

			$table->hits = 0;
			

			// Check the row.
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// Store the row.
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}
			
			$newId = $table->get('id');

			// Add the new ID to the array
			$newIds[$pk]	= $newId;
			
			// Store other new information
			PhocacartUtilsBatchhelper::storeProductItems($pk, (int)$newId);
			$dataCat[]		= (int)$categoryId;// categoryId - the category where we want to copy the products
			$currentDataCat = PhocacartCategoryMultiple::getAllCategoriesByProduct((int)$pk);// plus all other categories of this product
																							 // will be copied too
			// 1) Bind categories - destination category + all categories from source product (source product -> destination product)
			$dataCat2		= array_merge($dataCat, $currentDataCat);
			// 2) Remove duplicates
			$dataCat2		= array_unique($dataCat2);
			// 3) Remove the source category - we copy product from source category and the product is included in source category
			//    so don't copy it again to not get duplicates in the same category
			$currentCatidA 	= array(0 => (int)$currentCatid);
			$dataCat2 		= array_diff($dataCat2, $currentCatidA);
	
			PhocacartCategoryMultiple::storeCategories($dataCat2, (int)$newId);
			
			//$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Batch move articles to a new category
	 *
	 * @param   integer  $value  The new category ID.
	 * @param   array    $pks    An array of row IDs.
	 *
	 * @return  booelan  True if successful, false otherwise and internal error is set.
	 *
	 * @since	11.1
	 */
	protected function batchMove($value, $pks, $contexts)
	{
		$categoryId	= (int) $value;

		$table	= $this->getTable();
		//$db		= $this->getDbo();

		// Check that the category exists
		if ($categoryId) {
			$categoryTable = JTable::getInstance('PhocacartCategory', 'Table');
			if (!$categoryTable->load($categoryId)) {
				if ($error = $categoryTable->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
					return false;
				}
			}
		}

		if (empty($categoryId)) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
			return false;
		}

		// Check that user has create and edit permission for the component
		$extension	= JFactory::getApplication()->input->get('option');
		$user		= JFactory::getUser();
		if (!$user->authorise('core.create', $extension)) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
			return false;
		}

		if (!$user->authorise('core.edit', $extension)) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
			return false;
		}

		// Parent exists so we let's proceed
		foreach ($pks as $pk)
		{
			// Check that the row actually exists
			if (!$table->load($pk)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Set the new category ID
		//	$table->catid = $categoryId;

			// Check the row.
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// Store the row.
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}
			
			$dataCat[]	= (int)$categoryId;
			
			PhocacartCategoryMultiple::storeCategories($dataCat, (int)$table->id);
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}
	
	
	function recreate($cid = array(), &$message) {
		
		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'SELECT a.id, a.image, a.title'.
					' FROM #__phocacart_products AS a' .
					' WHERE a.id IN ( '.$cids.' )';
			$this->_db->setQuery($query);
			$files = $this->_db->loadObjectList();
			if (isset($files) && count($files)) {
				
				$msg = array();
				foreach($files as $k => $v) {
					
					$title 	= isset($v->title) ? $v->title : '';
					$title	= JText::_('COM_PHOCACART_PRODUCT') . ' ' . $v->title . ': ';
				
					if (isset($v->image) && $v->image != '') {
						
						$original	= PhocacartFile::existsFileOriginal($v->image, 'productimage');
						if (!$original) {
							// Original does not exist - cannot generate new thumbnail
							$msg[$k] = $title . JText::_('COM_PHOCACART_FILEORIGINAL_NOT_EXISTS');
							//return false;
							continue;
						}
						
						// Delete old thumbnails
						$deleteThubms = PhocacartFileThumbnail::deleteFileThumbnail($v->image, 1, 1, 1, 'productimage');
						if (!$deleteThubms) {
							$msg[$k] = $title . JText::_('COM_PHOCACART_ERROR_DELETE_THUMBNAIL');
							//return false;
							continue;
						}
						$createThubms = PhocacartFileThumbnail::getOrCreateThumbnail($v->image, 0, 1,1,1,0,'productimage');
						if (!$createThubms) {
							$msg[$k] = $title . JText::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');
							//return false;
							continue;
						}
						
						// Additional images
						if (isset($v->id) && (int)$v->id > 0) {
							$query = 'SELECT a.image'.
									' FROM #__phocacart_product_images AS a' .
									' WHERE a.product_id ='.(int)$v->id;
							$this->_db->setQuery($query);
							$files2 = $this->_db->loadObjectList();
							if (isset($files2) && count($files2)) {
								foreach($files2 as $k2 => $v2) {
								
									$original2	= PhocacartFile::existsFileOriginal($v2->image, 'productimage');
									if (!$original2) {
										// Original does not exist - cannot generate new thumbnail
										//$message = JText::_('COM_PHOCACART_FILEORIGINAL_NOT_EXISTS');
										//return false;
										continue;
									}
									
									// Delete old thumbnails
									$deleteThubms2 = PhocacartFileThumbnail::deleteFileThumbnail($v2->image, 1, 1, 1, 'productimage');
									if (!$deleteThubms2) {
										//$message = JText::_('COM_PHOCACART_ERROR_DELETE_THUMBNAIL');
										//return false;
										continue;
									}
									$createThubms2 = PhocacartFileThumbnail::getOrCreateThumbnail($v2->image, 0, 1,1,1,0,'productimage');
									if (!$createThubms2) {
										//$message = JText::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');
										//return false;
										continue;
									}
								
								}
							}
						}
						
					} else {
						$msg[$k] = $title . JText::_('COM_PHOCACART_FILENAME_NOT_EXISTS');
						//return false;
						continue;
					}
				}
				
				//$countMsg = count($msg);
				$message = !empty($msg) ? implode('<br />', $msg) : '';
				
			} else {
				$message = JText::_('COM_PHOCACART_ERROR_LOADING_DATA_DB');
				return false;
			}
		} else {
			$message = JText::_('COM_PHOCACART_ERROR_ITEM_NOT_SELECTED');
			return false;
		}
		return true;
	}
	
	public function featured($pks, $value = 0) {
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(JText::_('COM_PHOCACART_NO_ITEM_SELECTED'));
			return false;
		}

		$table = $this->getTable('PhocacartFeatured', 'Table');
		
		

		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->update($db->quoteName('#__phocacart_products'))
						->set('featured = ' . (int) $value)
						->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$db->execute();

			if ((int) $value == 0)
			{
				// Adjust the mapping table.
				// Clear the existing features settings.
				$query = $db->getQuery(true)
							->delete($db->quoteName('#__phocacart_product_featured'))
							->where('product_id IN (' . implode(',', $pks) . ')');
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// first, we find out which of our new featured articles are already featured.
				$query = $db->getQuery(true)
					->select('f.product_id')
					->from('#__phocacart_product_featured AS f')
					->where('product_id IN (' . implode(',', $pks) . ')');
				//echo $query;
				$db->setQuery($query);

				$old_featured = $db->loadColumn();

				// we diff the arrays to get a list of the articles that are newly featured
				$new_featured = array_diff($pks, $old_featured);

				// Featuring.
				$tuples = array();
				foreach ($new_featured as $pk)
				{
					$tuples[] = $pk . ', 0';
				}
				if (count($tuples))
				{
					$db = $this->getDbo();
					$columns = array('product_id', 'ordering');
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__phocacart_product_featured'))
						->columns($db->quoteName($columns))
						->values($tuples);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		$table->reorder();

		$this->cleanCache();

		return true;
	}
	
	
	/* Multiple categories */
	
	public function saveorder($pks = null, $order = null)
	{
		// PHOCAEDIT
		//$table = $this->getTable();
		$table 	= $this->getTable('PhocacartProductCategories', 'Table');
		
		// CURRENT CATEGORY
		$app 			= JFactory::getApplication('administrator');
		$currentCatid 	= $app->input->post->get('filter_category_id', 0, 'int');
		

		$tableClassName = get_class($table);
		$contentType = new JUcmType;
		$type = $contentType->getTypeByTable($tableClassName);
		$tagsObserver = $table->getObserverOfClass('JTableObserverTags');
		$conditions = array();
		

		if (empty($pks))
		{
			return $app->enqueueMessage(JText::_($this->text_prefix.'_ERROR_NO_ITEMS_SELECTED'), 'error');
			
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load(array('product_id' => (int) $pk, 'category_id' => (int)$currentCatid));
			
			

			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING);
			}
			elseif ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];

				
				if ($type)
				{
					$this->createTagsHelper($tagsObserver, $type, $pk, $type->type_alias, $table);
				}

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}

				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				
				$found = false;

				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$key = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}

		// Execute reorder for each category.
		
		foreach ($conditions as $cond)
		{
			
			$table->load(array('product_id' => (int) $cond[0], 'category_id' => (int)$currentCatid));
			
			$table->reorder($cond[1]);
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
	
	/*
	protected function getReorderConditions($table = null) {
		$condition = array();
		$condition[] = 'catid = '. (int) $table->catid;
		return $condition;
	}
	
	
	public function increaseOrdering($categoryId) {
		$ordering = 1;
		$this->_db->setQuery('SELECT MAX(ordering) FROM #__phocacart_products WHERE catid='.(int)$categoryId);
		$max = $this->_db->loadResult();
		$ordering = $max + 1;
		return $ordering;
	}*/
	
	protected function getReorderConditions($table = null) {
		$condition = array();
		$condition[] = 'category_id = '. (int) $table->category_id ;
		//$condition[] = 'product_id = '. (int) $table->product_id ;
		return $condition;
	}
	
	
	public function increaseOrdering($categoryId) {
		$ordering = 1;
		$this->_db->setQuery('SELECT MAX(ordering) FROM #__phocacart_product_categories WHERE category_id='.(int)$categoryId);
		$max = $this->_db->loadResult();
		$ordering = $max + 1;
		return $ordering;
	}
	
	protected function generateNewTitle($category_id, $alias, $title) {
		// Alter the title & alias
		$table = $this->getTable();
		// Product can be stored in different categories, so we ignore "parent id - category" - each product will have new name 
		// not like standard new name for each category
		while ($table->load(array('alias' => $alias))) {
			
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}
	
	public function copyattributes(&$cid = array(), $idSource = 0) {
		
		$app	= JFactory::getApplication();
		$cA = 0;
		
		if (count( $cid ) && (int)$idSource > 0) {
			JArrayHelper::toInteger($cid);
			
			// Attributes
			$aA = PhocacartAttribute::getAttributesById($idSource, 1);
			
			
			if (!empty($aA)) {
				foreach ($aA as $k => $v) {
					if (isset($v['id']) && $v['id'] > 0) {
						$oA = PhocacartAttribute::getOptionsById((int)$v['id'], 1);
						if (!empty($oA)) {
							$aA[$k]['options'] = $oA;
						}
					}
				}
				
			} else {
				$app->enqueueMessage(JText::_('COM_PHOCACART_SELECTED_SOURCE_PRODUCT_DOES_NOT_HAVE_ANY_ATTRIBUTES'), 'error');
				return false;
			}
			
			if (!empty($aA)) {
				foreach($cid as $k => $v) {
					
					if ((int)$v != $idSource) { // Do not copy to itself
						
						PhocacartAttribute::storeAttributesById((int)$v, $aA, 1);
						$cA++;
					}
				}
			}
			
		}
		
		if ((int)$cA > 0) {
			return true;
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_NO_ATTRIBUTE_COPIED'), 'error');
			return false;
		}
	}
}
?>