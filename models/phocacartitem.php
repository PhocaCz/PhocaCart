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
			$item->price 			= PhocaCartPrice::cleanPrice($item->price);
			$item->price_original 	= PhocaCartPrice::cleanPrice($item->price_original);
			$item->length 			= PhocaCartPrice::cleanPrice($item->length);
			$item->width 			= PhocaCartPrice::cleanPrice($item->width);
			$item->height 			= PhocaCartPrice::cleanPrice($item->height);
			$item->weight 			= PhocaCartPrice::cleanPrice($item->weight);
			$item->volume			= PhocaCartPrice::cleanPrice($item->volume);
			$item->unit_amount 		= PhocaCartPrice::cleanPrice($item->unit_amount);

		}

		return $item;
	}
	
	protected function prepareTable($table) {
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JApplication::stringURLSafe($table->alias);
		


		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->title);
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
		
		if ($data['alias'] == '') {
			$data['alias'] = $data['title'];
		}

		// Initialise variables;
		$app		= JFactory::getApplication();
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

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
			$thumb = PhocaCartFileThumbnail::getOrCreateThumbnail($table->image, '', 1, 1, 1, 0, 'productimage');
		}
					
		if ((int)$table->id > 0) {
			
			if (!isset($data['catid_multiple'])) {
				$data['catid_multiple'] = array();
			}
			PhocaCartCategoryMultiple::storeCategories($data['catid_multiple'], (int)$table->id);
			
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
			
			PhocaCartRelated::storeRelatedItemsById($dataRelated, (int)$table->id );
			
			if (!isset($data['attributes'])) {
				$data['attributes'] = array();
			}
			
			
			$pFormImg = $app->input->post->get('pformimg', array(), 'array');
			PhocaCartImageAdditional::storeImagesByProductId((int)$table->id, $pFormImg);
			$pFormAttr = $app->input->post->get('pformattr', array(), 'array');
			PhocaCartAttribute::storeAttributesById((int)$table->id, $pFormAttr);
			$pFormSpec = $app->input->post->get('pformspec', array(), 'array');
			PhocaCartSpecification::storeSpecificationsById((int)$table->id, $pFormSpec);
			
			
			
			
			if (!isset($data['tags'])) {
				$data['tags'] = array();
			}
			PhocaCartTag::storeTags($data['tags'], (int)$table->id);

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
			
			// 4B. DELETE FEATURED
			$query = 'DELETE FROM #__phocacart_product_featured'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			$tableF = $this->getTable('PhocaCartFeatured', 'Table');
			$tableF->reorder();
			
			// 5. DELETE IMAGES
			$query = 'DELETE FROM #__phocacart_product_images'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 6. DELETE REVIEWS
			$query = 'DELETE FROM #__phocacart_reviews'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 7. DELETE CATEGORY RELATIONSHIP
			$query = 'DELETE FROM #__phocacart_product_categories'
				. ' WHERE product_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 8. DELETE SPECIFICATIONS
			$query = 'DELETE FROM #__phocacart_specifications'
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
			$categoryTable = JTable::getInstance('PhocaCartCategory', 'Table');
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
		
		$i		= 0;

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
			$newIds[$i]	= $newId;
			
			// Store other new information
			PhocaCartBatchHelper::storeProductItems($pk, (int)$newId);
			$dataCat[]		= (int)$categoryId;// categoryId - the category where we want to copy the products
			$currentDataCat = PhocaCartCategoryMultiple::getAllCategoriesByProduct((int)$pk);// plus all other categories of this product
																							 // will be copied too
			// 1) Bind categories - destination category + all categories from source product (source product -> destination product)
			$dataCat2		= array_merge($dataCat, $currentDataCat);
			// 2) Remove duplicates
			$dataCat2		= array_unique($dataCat2);
			// 3) Remove the source category - we copy product from source category and the product is included in source category
			//    so don't copy it again to not get duplicates in the same category
			$currentCatidA 	= array(0 => (int)$currentCatid);
			$dataCat2 		= array_diff($dataCat2, $currentCatidA);
	
			PhocaCartCategoryMultiple::storeCategories($dataCat2, (int)$newId);
			
			$i++;
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
			$categoryTable = JTable::getInstance('PhocaCartCategory', 'Table');
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
			
			PhocaCartCategoryMultiple::storeCategories($dataCat, (int)$table->id);
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}
	
	
	function recreate($cid = array(), &$message) {
		
		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'SELECT a.id, a.image'.
					' FROM #__phocacart_products AS a' .
					' WHERE a.id IN ( '.$cids.' )';
			$this->_db->setQuery($query);
			$files = $this->_db->loadObjectList();
			if (isset($files) && count($files)) {
				foreach($files as $k => $v) {
				
					if (isset($v->image) && $v->image != '') {
						
						$original	= PhocaCartFile::existsFileOriginal($v->image, 'productimage');
						if (!$original) {
							// Original does not exist - cannot generate new thumbnail
							$message = JText::_('COM_PHOCACART_FILEORIGINAL_NOT_EXISTS');
							return false;
						}
						
						// Delete old thumbnails
						$deleteThubms = PhocaCartFileThumbnail::deleteFileThumbnail($v->image, 1, 1, 1, 'productimage');
						if (!$deleteThubms) {
							$message = JText::_('COM_PHOCACART_ERROR_DELETE_THUMBNAIL');
							return false;
						}
						$createThubms = PhocaCartFileThumbnail::getOrCreateThumbnail($v->image, 0, 1,1,1,0,'productimage');
						if (!$createThubms) {
							$message = JText::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');
							return false;
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
								
									$original2	= PhocaCartFile::existsFileOriginal($v2->image, 'productimage');
									if (!$original2) {
										// Original does not exist - cannot generate new thumbnail
										$message = JText::_('COM_PHOCACART_FILEORIGINAL_NOT_EXISTS');
										return false;
									}
									
									// Delete old thumbnails
									$deleteThubms2 = PhocaCartFileThumbnail::deleteFileThumbnail($v2->image, 1, 1, 1, 'productimage');
									if (!$deleteThubms2) {
										$message = JText::_('COM_PHOCACART_ERROR_DELETE_THUMBNAIL');
										return false;
									}
									$createThubms2 = PhocaCartFileThumbnail::getOrCreateThumbnail($v2->image, 0, 1,1,1,0,'productimage');
									if (!$createThubms2) {
										$message = JText::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');
										return false;
									}
								
								}
							}
						}
						
					} else {
						$message = JText::_('COM_PHOCACART_FILENAME_NOT_EXISTS');
						return false;
					}
				}
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

		$table = $this->getTable('PhocaCartFeatured', 'Table');
		
		

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
		$table 	= $this->getTable('PhocaCartProductCategories', 'Table');
		
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
}
?>