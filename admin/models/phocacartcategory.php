<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\LanguageHelper;
jimport('joomla.application.component.modeladmin');
use Joomla\String\StringHelper;

class PhocaCartCpModelPhocacartCategory extends AdminModel
{
	protected	$option 		    = 'com_phocacart';
	protected 	$text_prefix	        = 'com_phocacart';
	public $typeAlias 			        = 'com_phocacart.phocacartcategory';
    protected   $associationsContext    = 'com_phocacart.category';	// ASSOCIATION

	protected function canDelete($record) {
		$user = Factory::getUser();

		if (!empty($record->catid)) {
			return $user->authorise('core.delete', 'com_phocacart.phocadownloadcategory.'.(int) $record->catid);
		} else {
			return parent::canDelete($record);
		}
	}

	protected function canEditState($record){
		$user = Factory::getUser();

		if (!empty($record->catid)) {
			return $user->authorise('core.edit.state', 'com_phocacart.phocadownloadcategory.'.(int) $record->catid);
		} else {
			return parent::canEditState($record);
		}
	}

	public function getTable($type = 'PhocacartCategory', $prefix = 'Table', $config = array()){
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {

		$app	= Factory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartcategory', 'phocacartcategory', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData() {
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartcategory.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}

	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			if (isset($item->metadata)) {
				$registry = new Registry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}

			if (isset($item->params_feed)) {
				$registry = new Registry;
				$registry->loadString($item->params_feed);
				$item->params_feed = $registry->toArray();
			}

            // ASSOCIATION
            // Load associated Phoca Cart items
            $assoc = Associations::isEnabled();
            if ($assoc) {
                $item->associations = array();

                if ($item->id != null){
                    $associations = Associations::getAssociations('com_phocacart', '#__phocacart_categories', 'com_phocacart.category', $item->id, 'id', 'alias', false);

                    foreach ($associations as $tag => $association){
                        $item->associations[$tag] = $association->id;
                    }
                }
            }
		}
		return $item;
	}

	protected function prepareTable($table){


		jimport('joomla.filter.output');
		$date = Factory::getDate();
		$user = Factory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}


		$table->parent_id 	= PhocacartUtils::getIntFromString($table->parent_id);
		$table->date 		= PhocacartUtils::getDateFromString($table->date);

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_categories');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		else {
			// Set the values
			//$table->modified	= $date->toSql();
			//$table->modified_by	= $user->get('id');
		}
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'parent_id = '. (int) $table->parent_id;
		//$condition[] = 'state >= 0';
		return $condition;
	}

	public function save($data) {


		$app		= Factory::getApplication();
		$input  	= Factory::getApplication()->input;
		//$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();

		// Test thumbnail of category image
		if(isset($data['image']) && $data['image'] != '') {
			$thumb = PhocacartFileThumbnail::getOrCreateThumbnail($data['image'], '', 1, 1, 1, 0, 'categoryimage');
		}

		$user = Factory::getUser();


		// ALIAS
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0)) {
			if ($data['alias'] == null) {
				if (Factory::getConfig()->get('unicodeslugs') == 1) {
					$data['alias'] = OutputFilter::stringURLUnicodeSlug($data['title']);
				} else {
					$data['alias'] = OutputFilter::stringURLSafe($data['title']);
				}

				list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
				if ($data['alias'] != $alias) {
					Factory::getApplication()->enqueueMessage(Text::_('COM_PHOCACART_SAVE_WARNING'), 'warning');
					$data['alias'] = $alias;
				}
			}
		} else if ($table->load(array('alias' => $data['alias'], 'parent_id' => $data['parent_id'])) && ($table->id != $data['id'] || $data['id'] == 0)) {
			$this->setError(Text::_('COM_PHOCACART_ERROR_ITEM_UNIQUE_ALIAS'));
			return false;
		}

		if (!empty($data['feed'])) {
			$registry 	= new Registry($data['feed']);
			//$registry 	= new JRegistry($dataPh);
			$dataFeed 	= $registry->toString();
			if($dataFeed != '') {
				$data['params_feed'] = $dataFeed;
			}
		} else {
			$data['params_feed'] = '';
		}

		// Trigger the before event.
		PluginHelper::importPlugin('pca');

		// - START SAVE
		// ----------
		//$save = parent::save($data);

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);


		try {
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
				$table->date = Factory::getDate()->toSql();
			}

			if ($isNew) {
				$table->created = Factory::getDate()->toSql();
				$table->created_by = isset($user->id) ? (int)$user->id: 0;
			} else {
				$table->modified = Factory::getDate()->toSql();
				$table->modified_by = isset($user->id) ? (int)$user->id: 0;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// Trigger the before save event.
			$result = Factory::getApplication()->triggerEvent('onPCAonCategoryBeforeSave', array('com_phocacart.category', &$table, $isNew, $data));

			if (in_array(false, $result, true)) {
				$this->setError($table->getError());
				return false;
			}

			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}

			$this->cleanCache();

			// Trigger the after save event.
			Factory::getApplication()->triggerEvent('onPCAonCategoryAfterSave', array('com_phocacart.category', &$table, $isNew, $data));

		} catch (\Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		if (isset($table->$key)) {
			$this->setState($this->getName() . '.id', $table->$key);
		}

		$this->setState($this->getName() . '.new', $isNew);
		// - END SAVE
		// ----------


		if (!isset($data['group'])) {
			$data['group'] = array();
		}

		$savedId = $this->getState($this->getName().'.id');
		if ((int)$savedId > 0) {
			PhocacartGroup::storeGroupsById((int)$savedId, 2, $data['group']);
			PhocacartCount::setProductCount(array(0 => (int)$savedId), 'category', 1);
		}


		$pkName = $table->getKeyName();


        // ASSOCIATION
        if ((int)$savedId > 0 && $this->associationsContext && Associations::isEnabled() && !empty($data['associations'])) {
            $associations = $data['associations'];
            // Unset any invalid associations
            $associations = ArrayHelper::toInteger($associations);
            // Unset any invalid associations
            foreach ($associations as $tag => $id) {
                if (!$id){
                    unset($associations[$tag]);
                }
            }

            // Show a warning if the item isn't assigned to a language but we have associations.
            if ($associations && $table->language === '*') {
                Factory::getApplication()->enqueueMessage(
                    Text::_(strtoupper($this->option) . '_ERROR_ALL_LANGUAGE_ASSOCIATED'),
                    'warning'
                );
            }

            // Get associationskey for edited item
            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->select($db->qn('key'))
                ->from($db->qn('#__associations'))
                ->where($db->qn('context') . ' = ' . $db->quote($this->associationsContext))
                ->where($db->qn('id') . ' = ' . (int) $table->$pkName);
            $db->setQuery($query);
            $old_key = $db->loadResult();

            // Deleting old associations for the associated items
            $query = $db->getQuery(true)
                ->delete($db->qn('#__associations'))
                ->where($db->qn('context') . ' = ' . $db->quote($this->associationsContext));

            if ($associations) {
                $query->where('(' . $db->qn('id') . ' IN (' . implode(',', $associations) . ') OR '
                    . $db->qn('key') . ' = ' . $db->q($old_key) . ')');
            } else {
                $query->where($db->qn('key') . ' = ' . $db->q($old_key));
            }

            $db->setQuery($query);
            $db->execute();

            // Adding self to the association
            if ($table->language !== '*') {
                $associations[$table->language] = (int) $table->$pkName;
            }

            if (count($associations) > 1) {
                // Adding new association for these items
                $key   = md5(json_encode($associations));
                $query = $db->getQuery(true)
                    ->insert('#__associations');

                foreach ($associations as $id) {
                    $query->values(((int) $id) . ',' . $db->quote($this->associationsContext) . ',' . $db->quote($key));
                }
                $db->setQuery($query);
                $db->execute();
            }
        }

        if ($app->input->get('task') == 'editAssociations')
		{
			return $this->redirectToAssociations($data);
		}


		return true;
	}

	public function delete(&$cid = array()) {
		$app	= Factory::getApplication();
		$db 	= Factory::getDBO();

		$result = false;
		if (count( $cid )) {
			ArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$table = $this->getTable();
			if (!$this->canDelete($table)){
				$error = $this->getError();
				if ($error){
					Log::add($error, Log::WARNING);
					return false;
				} else {
					Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING);
					return false;
				}
			}

			// FIRST - if there are subcategories - - - - -
			$query = 'SELECT c.id, c.title, COUNT( s.parent_id ) AS numcat'
			. ' FROM #__phocacart_categories AS c'
			. ' LEFT JOIN #__phocacart_categories AS s ON s.parent_id = c.id'
			. ' WHERE c.id IN ( '.$cids.' )'
			. ' GROUP BY c.id, c.title, s.parent_id'
			;
			$db->setQuery( $query );

			if (!($rows2 = $db->loadObjectList())) {
				throw new Exception( Text::_('COM_PHOCACART_ERROR_PROBLEM_LOADING_DATA'), 500 );
				return false;
			}

			// Add new CID without categories which have subcategories (we don't delete categories with subcat)
			$err_cat = array();
			$cid 	 = array();
			foreach ($rows2 as $row) {
				if ($row->numcat == 0) {
					$cid[] = (int) $row->id;
				} else {
					$err_cat[] = $row->title;
				}
			}
			// - - - - - - - - - - - - - - -

			// Product with new cid - - - - -
			$err_img = array();
			if (count( $cid )) {
				ArrayHelper::toInteger($cid);
				$cids = implode( ',', $cid );

				// Select id's from product table, if there are some items, don't delete it.
				$query = 'SELECT c.id, c.title, COUNT( s.category_id ) AS numproduct'
				. ' FROM #__phocacart_categories AS c'
				. ' LEFT JOIN #__phocacart_product_categories AS s ON s.category_id = c.id'
				. ' WHERE c.id IN ( '.$cids.' )'
				. ' GROUP BY c.id, c.title, s.category_id';

				$db->setQuery( $query );

				if (!($rows = $db->loadObjectList())) {
					throw new Exception( Text::_('COM_PHOCACART_ERROR_PROBLEM_LOADING_DATA'), 500 );
					return false;
				}



				$cid 	 = array();
				foreach ($rows as $row) {
					if ($row->numproduct == 0) {
						$cid[] = (int) $row->id;
					} else {
						$err_img[] = $row->title;
					}
				}

				if (count( $cid )) {
					$cids = implode( ',', $cid );
					$query = 'DELETE FROM #__phocacart_categories'
					. ' WHERE id IN ( '.$cids.' )';
					$db->setQuery( $query );
					if (!$db->execute()) {
						$this->setError($db->getErrorMsg());
						return false;
					}

					// 7. DELETE CATEGORY RELATIONSHIP (should not happen as this should be deleted when products are deleted)
					$query = 'DELETE FROM #__phocacart_product_categories'
						. ' WHERE category_id IN ( '.$cids.' )';
					$db->setQuery( $query );
					$db->execute();

					// Delete items in phocadownload_user_category
				/*	$query = 'DELETE FROM #__phocadownload_user_category'
					. ' WHERE catid IN ( '.$cids.' )';
					$db->setQuery( $query );
					if (!$db->query()) {
						$this->setError($row->getError());
						return false;
					}*/

					// 9. DELETE PRODUCT CUSTOMER GROUPS
					$query = 'DELETE FROM #__phocacart_item_groups'
						. ' WHERE item_id IN ( '.$cids.' )'
						. ' AND type = 2';
					$db->setQuery( $query );
					$db->execute();
				}
			}

			// There are some images in the category - don't delete it
			$msg = '';
			if (!empty( $err_cat ) || !empty( $err_img )) {
				if (!empty( $err_cat )) {
					$cids_cat = implode( ", ", $err_cat );
					$msg .= Text::plural( 'COM_PHOCACART_ERROR_DELETE_CONTAIN_CATEGORY', $cids_cat );
				}

				if (!empty( $err_img )) {
					$cids_img = implode( ", ", $err_img );
					$msg .= Text::plural( 'COM_PHOCACART_ERROR_DELETE_CONTAIN_PRODUCT', $cids_img );
				}
				$link = 'index.php?option=com_phocacart&view=phocacartcategories';
				$app->enqueueMessage($msg, 'error');
				$app->redirect($link);
			}
		}
		return true;
	}

	protected function batchCopy($value, $pks, $contexts)
	{
		$categoryId	= (int) $value;


		$table	= $this->getTable();
		$db		= $this->getDbo();

		// Check that the category exists
		if ($categoryId) {
			$categoryTable = Table::getInstance('PhocacartCategory', 'Table');

			if (!$categoryTable->load($categoryId)) {
				if ($error = $categoryTable->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
					return false;
				}
			}
		}

		//if (empty($categoryId)) {
		if (!isset($categoryId)) {
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
			return false;
		}

		// Check that the user has create permission for the component
		$extension	= Factory::getApplication()->input->get('option');
		$user		= Factory::getUser();
		if (!$user->authorise('core.create', $extension)) {
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
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
					$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$data = $this->generateNewTitle($categoryId, $table->alias, $table->title);
			$table->title   = $data['0'];
			$table->alias   = $data['1'];
			$table->published = 0;// As default the copied new category is unpublished

			// Reset the ID because we are making a copy
			$table->id		= 0;

			// New category ID
			$table->parent_id	= $categoryId;

			// Ordering
			$table->ordering = $this->increaseOrdering($categoryId);

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

			// Get the new item ID
			$newId = $table->get('id');

			// Add the new ID to the array
			$newIds[$pk]	= $newId;
			// Store other new information
			PhocacartUtilsBatchhelper::storeCategoryItems($pk, (int)$newId);


			//$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	protected function batchMove($value, $pks, $contexts)
	{
		$categoryId	= (int) $value;

		$table	= $this->getTable();
		//$db		= $this->getDbo();
		$app	= Factory::getApplication();

		// Check that the category exists
		if ($categoryId) {
			$categoryTable = Table::getInstance('PhocacartCategory', 'Table');
			if (!$categoryTable->load($categoryId)) {
				if ($error = $categoryTable->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
					return false;
				}
			}
		}

		//if (empty($categoryId)) {
		if (!isset($categoryId)) {
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
			return false;
		}

		// Check that user has create and edit permission for the component
		$extension	= Factory::getApplication()->input->get('option');
		$user		= Factory::getUser();
		if (!$user->authorise('core.create', $extension)) {
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
			return false;
		}

		if (!$user->authorise('core.edit', $extension)) {
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
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
					$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Set the new category ID
			$table->parent_id = $categoryId;

			// Alter the title & alias
			list($title, $alias) = $this->generateNewTitle($categoryId, $table->alias, $table->title);
			if ($table->alias != $alias) {
				Factory::getApplication()->enqueueMessage(Text::_('COM_PHOCACART_SAVE_WARNING'), 'warning');
				$table->alias = $alias;
			}

			// Cannot move the node to be a child of itself.
			if ((int)$table->id == (int)$categoryId) {
				$app->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_INVALID_NODE_RECURSION', get_class($pk)));
				return false;
			}

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
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}


	public function increaseOrdering($categoryId) {

		$ordering = 1;
		$this->_db->setQuery('SELECT MAX(ordering) FROM #__phocacart_categories WHERE parent_id='.(int)$categoryId);
		$max = $this->_db->loadResult();
		$ordering = $max + 1;
		return $ordering;
	}


	public function batch($commands, $pks, $contexts)
	{

		// Sanitize user ids.
		$pks = array_unique($pks);
		ArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true)) {
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks)) {
			$this->setError(Text::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;

		if (!empty($commands['assetgroup_id'])) {
			if (!$this->batchAccess($commands['assetgroup_id'], $pks)) {
				return false;
			}

			$done = true;
		}

		//PHOCAEDIT - Parent is by Phoca 0 not 1 like by Joomla!
		$comCat =false;
		if ($commands['category_id'] == '') {
			$comCat = false;
		} else if ( $commands['category_id'] == '0') {
			$comCat = true;
		} else if ((int)$commands['category_id'] > 0) {
			$comCat = true;
		}

		if ($comCat)
		//if (isset($commands['category_id']))
		{
			$cmd = ArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c')
			{
				$result = $this->batchCopy($commands['category_id'], $pks, $contexts);
				if (is_array($result))
				{
					$pks = $result;
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMove($commands['category_id'], $pks, $contexts))
			{
				return false;
			}
			$done = true;
		}

		if (!empty($commands['language_id']))
		{
			if (!$this->batchLanguage($commands['language_id'], $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done) {
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}


	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias, 'parent_id' => $category_id)))
		{

			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	function recreate($cid = array(), &$message = '') {

		if (count( $cid )) {
			ArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'SELECT a.image, a.title'.
					' FROM #__phocacart_categories AS a' .
					' WHERE a.id IN ( '.$cids.' )';
			$this->_db->setQuery($query);
			$files = $this->_db->loadObjectList();
			if (isset($files) && count($files)) {

				$msg = array();
				foreach($files as $k => $v) {

					$title 	= isset($v->title) ? $v->title : '';
					$title	= Text::_('COM_PHOCACART_CATEGORY') . ' ' . $v->title . ': ';

					if (isset($v->image) && $v->image != '') {



						$original	= PhocacartFile::existsFileOriginal($v->image, 'categoryimage');
						if (!$original) {
							// Original does not exist - cannot generate new thumbnail
							$msg[$k] = $title . Text::_('COM_PHOCACART_FILEORIGINAL_NOT_EXISTS');
							//return false;
							continue;
						}

						// Delete old thumbnails
						$deleteThubms = PhocacartFileThumbnail::deleteFileThumbnail($v->image, 1, 1, 1, 'categoryimage');
						if (!$deleteThubms) {
							$msg[$k] = $title. Text::_('COM_PHOCACART_ERROR_DELETE_THUMBNAIL');
							//return false;
							continue;
						}
						$createThubms = PhocacartFileThumbnail::getOrCreateThumbnail($v->image, 0, 1,1,1,0,'categoryimage');
						if (!$createThubms) {
							$msg[$k] = $title . Text::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');
							//return false;
							continue;
						}

					} else {
						//$msg[$k] = $title . JText::_('COM_PHOCACART_FILENAME_NOT_EXISTS');
						$msg[$k] = $title . Text::_('COM_PHOCACART_CATEGORY_IMAGE_NOT_EXISTS');
						//return false;
						continue;
					}
				}

				$message = !empty($msg) ? implode('<br />', $msg) : '';

			} else {
				$message = Text::_('COM_PHOCACART_ERROR_LOADING_DATA_DB');
				return false;
			}
		} else {
			$message = Text::_('COM_PHOCACART_ERROR_ITEM_NOT_SELECTED');
			return false;
		}
		return true;
	}

    // ASSOCIATION
    protected function preprocessForm(Form $form, $data, $group = 'content'){
        /*if ($this->canCreateCategory())
        {
            $form->setFieldAttribute('catid', 'allowAdd', 'true');
        }*/

        // Association Phoca Cart items
        if (Associations::isEnabled()){
            $languages = LanguageHelper::getContentLanguages(false, true, null, 'ordering', 'asc');

            if (count($languages) > 1){
                $addform = new SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language)
                {

                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'Modal_Phocacartcategory');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                    $field->addAttribute('propagate', 'true');
                }

                $form->load($addform, false);
            }
        }


		// Load Feed Forms - by Plugin
		$feedPlugins = PhocacartFeed::getFeedPluginMethods();

		if (!empty($feedPlugins)) {
			foreach ($feedPlugins as $k => $v) {

				$element = htmlspecialchars($v->element, ENT_QUOTES, 'UTF-8');
				$addformF = new SimpleXMLElement('<form />');
				$fields = $addformF->addChild('fields');
				$fields->addAttribute('name', 'feed');
				//$fields->addAttribute('addfieldpath', 'associations');
				$fieldset = $fields->addChild('fieldset');
				$fieldset->addAttribute('name', 'feed_'.$element);
				$fieldset->addAttribute('group', 'pcf');

				$field = $fieldset->addChild('field');
				$field->addAttribute('name', $element);
				$field->addAttribute('type', 'subform');

				$field->addAttribute('label', Text::_(strtoupper($v->name)));
				$field->addAttribute('multiple', 'false');
				$field->addAttribute('layout', 'joomla.form.field.subform.default');
				$field->addAttribute('formsource', 'plugins/pcf/'.$element.'/models/forms/category.xml');
				$field->addAttribute('clear', 'true');
				$field->addAttribute('propagate', 'true');
				$form->load($addformF, false);
			}


		}

        parent::preprocessForm($form, $data, $group);
    }


	public function featured($pks, $value = 0) {
		// Sanitize the ids.
		$pks = (array) $pks;
		ArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(Text::_('COM_PHOCACART_NO_ITEM_SELECTED'));
			return false;
		}

		//$table = $this->getTable('PhocacartCategoryFeatured', 'Table');



		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->update($db->quoteName('#__phocacart_categories'))
						->set('featured = ' . (int) $value)
						->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$db->execute();

			/*if ((int) $value == 0)
			{
				// Adjust the mapping table.
				// Clear the existing features settings.
				$query = $db->getQuery(true)
							->delete($db->quoteName('#__phocacart_category_featured'))
							->where('product_id IN (' . implode(',', $pks) . ')');
				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// first, we find out which of our new featured articles are already featured.
				$query = $db->getQuery(true)
					->select('f.product_id')
					->from('#__phocacart_category_featured AS f')
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
						->insert($db->quoteName('#__phocacart_category_featured'))
						->columns($db->quoteName($columns))
						->values($tuples);
					$db->setQuery($query);
					$db->execute();
				}
			}*/
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		//$table->reorder();

		$this->cleanCache();

		return true;
	}

}
?>
