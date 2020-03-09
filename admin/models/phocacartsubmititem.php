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

class PhocaCartCpModelPhocaCartSubmititem extends JModelAdmin
{
	protected	$option 		        = 'com_phocacart';
	protected 	$text_prefix	        = 'com_phocacart';
	public      $typeAlias 		        = 'com_phocacart.phocacartsubmititem';
	protected   $associationsContext    = 'com_phocacart.submititem';	// ASSOCIATION

	protected function canDelete($record){
		$user = JFactory::getUser();

		if (!empty($record->catid)) {
			// catid not used
			return $user->authorise('core.delete', 'com_phocacart.phocacartsubmititem.'.(int) $record->catid);
		} else {
			return parent::canDelete($record);
		}
	}

	protected function canEditState($record) {
		$user = JFactory::getUser();

		if (!empty($record->catid)) {
			// catid not used
			return $user->authorise('core.edit.state', 'com_phocacart.phocacartsubmititem.'.(int) $record->catid);
		} else {
			return parent::canEditState($record);
		}
	}

	public function getTable($type = 'PhocaCartSubmititem', $prefix = 'Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {

		$app	= JFactory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartsubmititem', 'submit', array('control' => 'jform', 'load_data' => $loadData));

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
			if (isset($item->metadata)) {
				$registry = new JRegistry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}

			if (isset($item->params_feed)) {
				$registry = new JRegistry;
				$registry->loadString($item->params_feed);
				$item->params_feed = $registry->toArray();
			}


			if (isset($item->items_item)) {
				$registry = new JRegistry;
				$registry->loadString($item->items_item);
				$itemI = $registry->toArray();
				$item->items_item = $itemI;

			}

			if (isset($item->items_contact)) {
				$registry = new JRegistry;
				$registry->loadString($item->items_contact);
				$contactI = $registry->toArray();
				$item->items_contact = $contactI;
			}

			if (isset($item->items_parameter)) {
				$registry = new JRegistry;
				$registry->loadString($item->items_parameter);
				$parameterI = $registry->toArray();
				$item->items_parameter = $parameterI;
			}



		}

		return $item;
	}

	protected function prepareTable($table) {
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title					= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias					= JApplicationHelper::stringURLSafe($table->alias);


		if (empty($table->alias)) {
			$table->alias = JApplicationHelper::stringURLSafe($table->title);
		}

	}


	function save($data) {

		$app		= JFactory::getApplication();
		$input  	= JFactory::getApplication()->input;
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;


		$params = PhocacartUtils::getComponentParameters();
		//$submit_item_max_char_textarea 	= $params->get('submit_item_max_char_textarea', 5000);
		$submit_item_form_fields 		= $params->get('submit_item_form_fields', '');
		$items = array_map('trim', explode(',', $submit_item_form_fields));
		$items = array_unique($items);

		$submit_item_form_fields_contact = $params->get('submit_item_form_fields_contact', '');
		$itemsC = array_map('trim', explode(',', $submit_item_form_fields_contact));
		$itemsC = array_unique($itemsC);

		$submit_item_form_fields_parameters	= $params->get( 'submit_item_form_fields_parameters', '' );
		$itemsP = array_map('trim', explode(',', $submit_item_form_fields_parameters));
		$itemsP = array_unique($itemsP);


		$item 		= array();
		$contact 	= array();
		$parameter 	= array();

		if (!empty($items)) {
			foreach ($items as $k => $v) {
				$v = trim(str_replace('*', '', $v));
				if (isset($data['items_item'][$v]) && $data['items_item'][$v] != '') {
					$item[$v] = $data['items_item'][$v];

				}
			}
		}

		if (!empty($itemsC)) {
			foreach ($itemsC as $k => $v) {
				$v = trim(str_replace('*', '', $v));
				if (isset($data['items_contact'][$v]) && $data['items_contact'][$v] != '') {
					$contact[$v] = $data['items_contact'][$v];
				}
			}
		}


		if (!empty($itemsP)) {
			$parameters = PhocacartParameter::getAllParameters('alias');
			foreach ($itemsP as $k => $v) {
				$v = trim(str_replace('*', '', $v));
				$vId   	= 0;
                if (isset($parameters[$v]->id) && $parameters[$v]->id > 0) {
                    $vId = (int)$parameters[$v]->id;
                }
                if (!empty($data['items_parameter'][$vId])) {
                	$parameter[$vId] = $data['items_parameter'][$vId];
                }
			}
		}

		$tempData 			= $data;
		$data 				= array();
		$data 				= $tempData;
		$data['items_item'] = json_encode($item);
		$data['items_contact'] = json_encode($contact);
		$data['items_parameter'] = json_encode($parameter);
		$data['date_submit']= $tempData['date_submit'];
		$data['user_id'] 	= $tempData['user_id'];
		$data['title'] 		= $tempData['title'];
		$data['alias']		= PhocacartUtils::getAliasName($data['title']);
		$data['ordering']	= $this->increaseOrdering();
		$data['published']	= 1;

		$data['upload_token'] 			= PhocacartUtils::getToken();
		$data['upload_folder']			= PhocacartUtils::getToken('folder');



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
			//$this->setError(\JText::_('COM_PHOCACART_ERROR_ITEM_UNIQUE_ALIAS'));
			//return false;
		}

		// Include the content plugins for the on save events.
		//JPluginHelper::importPlugin('content');

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

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		if ((int)$table->id > 0) {

		}

		// Clean the cache.
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		// Trigger the onContentAfterSave event.
		//\JFactory::getApplication()->triggerEvent('$this->event_after_save, array($this->option.'.'.$this->name, $table, $isNew));

		$pkName = $table->getKeyName();
		if (isset($table->$pkName)) {
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);


		return true;
	}

	public function delete(&$cid = array()) {

		$app = JFactory::getApplication();

		if (count( $cid )) {
			\Joomla\Utilities\ArrayHelper::toInteger($cid);
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
			$query = 'DELETE FROM #__phocacart_submit_items' . ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();


			// 2. DELETE UPLOAD FOLDERS
			$query = 'SELECT upload_folder FROM #__phocacart_submit_items'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$items = $this->_db->loadObjectList();

			if (!empty($items)) {
				$pathSubmit = PhocacartPath::getPath('submititem');
				foreach($items as $k => $v) {
					if (isset($v->upload_folder) && $v->upload_folder != '') {
						if (\Joomla\CMS\Filesystem\Folder::exists($pathSubmit['orig_abs_ds'] . $v->upload_folder)) {
							if (!\Joomla\CMS\Filesystem\Folder::delete($pathSubmit['orig_abs_ds'] . $v->upload_folder)) {
								$msg = JText::_('COM_PHOCACART_FOLDER') . ': ' . $v->upload_folder . " - " . JText::_('COM_PHOCACART_ERROR_FOLDER_NOT_DELETED');
								$app->enqueueMessage($msg, 'error');
							}
						}
					}
				}

			}
		}
		return true;
	}



	function create($cid = array(), &$message) {

		if (count( $cid )) {


			$paramsC = PhocacartUtils::getComponentParameters();
			$chunkMethod = $paramsC->get('multiple_upload_chunk', 0);

			$sIMoveImgFolder 				= $paramsC->get('submit_item_move_image_folder', 0);
			$sIMoveImageFolderSpecific 		= $paramsC->get('submit_item_move_image_folder_specific', '');
			$sIRemoveItemCreateProduct 		= $paramsC->get('submit_item_remove_item_create_product', 0);
			$sIConvertMarkdownCreateProduct = $paramsC->get('submit_item_convert_markdown_create_product', 0);


			if ($sIMoveImgFolder == 1 && PhocacartText::filterValue($sIMoveImageFolderSpecific, 'folder') == '') {
				$sIMoveImgFolder = 0;
			}

			$pathImage = PhocacartPath::getPath('productimage');
			$pathSubmit = PhocacartPath::getPath('submititem');

			\Joomla\Utilities\ArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'SELECT a.*'.
					' FROM #__phocacart_submit_items AS a' .
					' WHERE a.id IN ( '.$cids.' )';
			$this->_db->setQuery($query);
			$items = $this->_db->loadObjectList();

			if (isset($items) && count($items)) {

				$table		= $this->getTable('PhocaCartItem');

				$msg = array();
				$addedItems = 0;
				$notAddedItems = 0;
				foreach($items as $k => $v) {

					if (isset($v->items_item)) {
						$data = json_decode($v->items_item, true);


						// Test if item includes category
						if (!empty($data['catid_multiple']) && isset($data['catid_multiple'][0]) && (int)$data['catid_multiple'][0] > 0) {

						} else {
							$msg[] = JText::_('COM_PHOCACART_ITEM') . ': ' . $v->title . " - ". JText::_('COM_PHOCACART_ERROR_PRODUCT_FROM_ITEM_NOT_CREATED_NO_CATEGORY');
							$notAddedItems++;
							continue;
						}


						// Images
						$iI = 0;
						$dataImage 				= '';
						$dataImageAdditional 	= array();
						$dataImageMove			= array();
						if (!empty($data['image'])) {
							foreach($data['image'] as $kI => $vI) {

								if ($iI == 0) {
									// First image as image

									$dataImage = $vI['name'];
								} else {
									// All others as additional images
									$dataImageAdditional[$iI]['id'] = 0;
									$dataImageAdditional[$iI]['image'] = $vI['name'];

								}

								// 0) Root folder of phoca cart product images
								$folder = '';
								$dataImageMove[$iI]['dest'] = $pathImage['orig_abs_ds'] . $vI['name'];
								if ($sIMoveImgFolder == 1) {

									// 1) Specific folder
									$folder = PhocacartText::filterValue($sIMoveImageFolderSpecific, 'folder');
									if ($folder != '') {
										if (!Joomla\CMS\Filesystem\Folder::exists($pathImage['orig_abs_ds'] . $folder)) {
											if (!Joomla\CMS\Filesystem\Folder::create($pathImage['orig_abs_ds'] . $folder)) {
												$msg[] = JText::_('COM_PHOCACART_FOLDER') . ': ' . $folder . " - ". JText::_('COM_PHOCACART_ERROR_FOLDER_NOT_CREATED');
											}
										}

										$folder = $folder . '/';
									}

									if ($folder != '') {
										$dataImageMove[$iI]['dest'] = $pathImage['orig_abs_ds'] . $folder  . $vI['name'];
									}



								} else if ($sIMoveImgFolder == 2 || $sIMoveImgFolder == 3) {

									// 2) Folder created from first letter of file or title
									$folder = PhocacartText::filterValue(substr($data['title'], 0, 1));
									if ($sIMoveImgFolder == 2) {
										$folder = PhocacartText::filterValue(substr($vI['name'], 0, 1));
									}

									if ($folder != '') {
										if (!Joomla\CMS\Filesystem\Folder::exists($pathImage['orig_abs_ds'] . $folder)) {
											if (!Joomla\CMS\Filesystem\Folder::create($pathImage['orig_abs_ds'] . $folder)) {
												$msg[] = JText::_('COM_PHOCACART_FOLDER') . ': ' . $folder . " - ". JText::_('COM_PHOCACART_ERROR_FOLDER_NOT_CREATED');
											}
										}

										$folder = $folder . '/';
									}

									if ($folder != '') {
										$dataImageMove[$iI]['dest'] = $pathImage['orig_abs_ds'] . $folder .  $vI['name'];
									}
								}

								$dataImageMove[$iI]['src'] = $vI['fullpath'];
								$dataImageMove[$iI]['image'] = $vI['name'];
								$dataImageMove[$iI]['fileno'] = $folder . $vI['name'];
								$iI++;

							}
						}

						$data['image'] = $dataImage;
						$data['user_id'] = $v->user_id;

						if ($sIConvertMarkdownCreateProduct == 1) {
							if (!class_exists('Parsedown')) {
								require_once(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/Parsedown/Parsedown.php');
							}
							$p = new Parsedown();
							$data['description'] = $p->text($data['description']);
							$data['description_long'] = $p->text($data['description_long']);
							$data['features'] = $p->text($data['features']);
						}


						// TEST - REMOVE in STABLE
						//$table->id = 1189;



						// Bind the data.
						if (!$table->bind($data)) {
							$this->setError($table->getError());
							$message = $table->getError();
							return false;
						}

						//$this->prepareTable($table);

						// Check the data.
						if (!$table->check()) {
							$this->setError($table->getError());
							$message = $table->getError();
							return false;
						}


						if (!$table->store()) {
							$this->setError($table->getError());
							$message = $table->getError();
							return false;
						}

						// TEST - REMOVE in STABLE
						//$table->id = 1189;


						if ((int)$table->id > 0) {

							$data['items_parameter'] = json_decode($v->items_parameter, true);

							$previousManufacturers = array();
							$currentManufacturers = isset($data['manufacturer_id']) && (int)$data['manufacturer_id'] > 0 ? array(0 => (int)$data['manufacturer_id']) : array();
							$allManufacturers = array_unique(array_merge($previousManufacturers, $currentManufacturers));
							PhocacartCount::setProductCount($allManufacturers, 'manufacturer', 1);// We need to recount all manufacturers - previous (now deleted), and new

							if (!isset($data['catid_multiple'])) {
								$data['catid_multiple'] = array();
							}

							$previousCategories = PhocacartCategoryMultiple::getCategories((int)$table->id, 1);
							PhocacartCategoryMultiple::storeCategories($data['catid_multiple'], (int)$table->id);
							$allCategories = array_unique(array_merge($previousCategories, $data['catid_multiple']));
							PhocacartCount::setProductCount($allCategories, 'category', 1);// We need to recount all categories - previous (now deleted), and new
							PhocacartImageAdditional::storeImagesByProductId((int)$table->id, $dataImageAdditional);

							// TAGS
							if (!isset($data['tags'])) {
								$data['tags'] = array();
							}

							$previousTags = PhocacartTag::getTags((int)$table->id, 1);
							PhocacartTag::storeTags($data['tags'], (int)$table->id);
							$allTags = array_unique(array_merge($previousTags, $data['tags']));
							PhocacartCount::setProductCount($allTags, 'tag', 1);// We need to update product count even for values which were removed when editing ($allTags)

							// TAG LABELS
							if (!isset($data['taglabels'])) {
								$data['taglabels'] = array();
							}

							$previousLabels = PhocacartTag::getTagLabels((int)$table->id, 1);
							PhocacartTag::storeTagLabels($data['taglabels'], (int)$table->id);
							$allLabels = array_unique(array_merge($previousLabels, $data['taglabels']));
							PhocacartCount::setProductCount($allLabels, 'label', 1);// We need to update product count even for values which were removed when editing ($allLabels)


							// PARAMETERS
							$parameters = PhocacartParameter::getAllParameters();
							if (!empty($parameters)) {
								foreach ($parameters as $kP => $vP) {
									if (isset($vP->id) && (int)$vP->id > 0) {
										$idP = (int)$vP->id;

										if (empty($data['items_parameter'][$idP])) {
											$data['items_parameter'][$idP] = array();
										}

										$previousParameterValues = PhocacartParameter::getParameterValues((int)$table->id, $idP, 1);
										PhocacartParameter::storeParameterValues($data['items_parameter'][$idP], (int)$table->id, $idP);
										$allParameterValues = array_unique(array_merge($previousParameterValues, $data['items_parameter'][$idP]));
										PhocacartCount::setProductCount($allParameterValues, 'parameter', 1);
									}
								}
							}


							// Copy images to product image folder
							if (!empty($dataImageMove)) {
								foreach($dataImageMove as $kIm => $vIm) {


									if (!\Joomla\CMS\Filesystem\File::copy($vIm['src'], $vIm['dest'])) {
										$msg[] = JText::_('COM_PHOCACART_IMAGE') . ': ' . $vIm['image'] . " - ". JText::_('COM_PHOCACART_ERROR_IMAGE_NOT_COPIED');

									} else {
										$createThubms = PhocacartFileThumbnail::getOrCreateThumbnail($vIm['fileno'], 0, 1, 1, 1, 0, 'productimage');
										if (!$createThubms) {
											$msg[] = $vIm['image'] . JText::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');

										}
									}
								}
							}

							$msg[] = JText::_('COM_PHOCACART_ITEM') . ': ' . $v->title . " - ". JText::_('COM_PHOCACART_SUCCESS_PRODUCT_FROM_ITEM_CREATED');
							$addedItems++;

						}

					}


					if ($sIRemoveItemCreateProduct == 1) {
						// Delete the submit item folder for all images
						if (isset($v->upload_folder) && $v->upload_folder != '') {
							if (\Joomla\CMS\Filesystem\Folder::exists($pathSubmit['orig_abs_ds'] . $v->upload_folder)) {
								if (!\Joomla\CMS\Filesystem\Folder::delete($pathSubmit['orig_abs_ds'] . $v->upload_folder)) {
									$msg[] = JText::_('COM_PHOCACART_FOLDER') . ': ' . $v->upload_folder . " - " . JText::_('COM_PHOCACART_ERROR_FOLDER_NOT_DELETED');

								}
							}
						}


						// Delete the submit item after moving to product
						if (isset($v->id) && $v->id > 0) {
							$query = 'DELETE FROM #__phocacart_submit_items'
								. ' WHERE id = ' . (int)$v->id;
							$this->_db->setQuery($query);
							$this->_db->execute();
						}
					}
				}

				//$countMsg = count($msg);
				$message = !empty($msg) ? implode('<br />', $msg) : '';

				if ($addedItems == 0) {

					return false;
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


	public function increaseOrdering() {
		$ordering = 1;
		$this->_db->setQuery('SELECT MAX(ordering) FROM #__phocacart_submit_items');
		$max = $this->_db->loadResult();
		$ordering = $max + 1;
		return $ordering;
	}

	protected function generateNewTitle($category_id, $alias, $title) {

		$app 			= JFactory::getApplication('administrator');
		$batchParams 	= $app->input->post->get('batch', array(), 'array');


		// Alter the title & alias
		$table = $this->getTable();
		// Product can be stored in different categories, so we ignore "parent id - category" - each product will have new name
		// not like standard new name for each category
		while ($table->load(array('alias' => $alias))) {

			// Skip creating unique name
			if (isset($batchParams['skip_creating_unique_name']) && $batchParams['skip_creating_unique_name'] == 1) {

			} else {
				$title = StringHelper::increment($title);
			}
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array($title, $alias);
	}


	// ASSOCIATION
	protected function preprocessForm(JForm $form, $data, $group = 'content'){
		/*if ($this->canCreateCategory())
		{
			$form->setFieldAttribute('catid', 'allowAdd', 'true');
		}*/

		// Association Phoca Cart items
		if (JLanguageAssociations::isEnabled()){
			$languages = JLanguageHelper::getContentLanguages(false, true, null, 'ordering', 'asc');

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
					$field->addAttribute('type', 'Modal_Phocacartitem');
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

				$field->addAttribute('label', JText::_(strtoupper($v->name)));
				$field->addAttribute('multiple', 'false');
				$field->addAttribute('layout', 'joomla.form.field.subform.default');
				$field->addAttribute('formsource', 'plugins/pcf/'.$element.'/models/forms/item.xml');
				$field->addAttribute('clear', 'true');
				$field->addAttribute('propagate', 'true');
				$form->load($addformF, false);
			}


		}


		// Load Parameter Values for Parameters
		$parameters = PhocacartParameter::getAllParameters();

		// Items and Items (Contact) are defined in view
		// Items (Parameters) will be defined here

		$pC 		= PhocacartUtils::getComponentParameters();

		// Items and Items (Contact) are defined in this view
		// Items (Parameters) will be defined model (when creating the form)

		$submit_item_form_fields_parameters	= $pC->get( 'submit_item_form_fields_parameters', '' );


		if($submit_item_form_fields_parameters != '') {
			$itemsP = array_map('trim', explode(',', $submit_item_form_fields_parameters));
			$itemsP = array_unique($itemsP);



			if (count($parameters) > 0 && !empty($itemsP)) {
				$addform = new SimpleXMLElement('<form />');
				$fields = $addform->addChild('fields');
				$fields->addAttribute('name', 'items_parameter');
				$fieldset = $fields->addChild('fieldset');
				$fieldset->addAttribute('name', 'items_parameter');

				foreach ($parameters as $k => $v) {



					$isIncluded = 0;
					if (in_array($v->alias, $itemsP)) {
						$isIncluded = 1;// included
					}
					if (in_array($v->alias . '*', $itemsP)) {
						$isIncluded = 2;// included and required
					}


					if ($isIncluded > 0) {

						$field = $fieldset->addChild('field');
						$field->addAttribute('name', $v->id);
						$field->addAttribute('parameterid', $v->id);
				        $field->addAttribute('parameteralias', $v->alias);
						$field->addAttribute('type', 'PhocaCartParameterValuesSubmitItems');
						//$field->addAttribute('language', $language->lang_code);
						$field->addAttribute('label', $v->title);
						$field->addAttribute('class', 'chosen-select');
						$field->addAttribute('multiple', 'true');
						$field->addAttribute('translate_label', 'false');
						$field->addAttribute('select', 'true');
						$field->addAttribute('new', 'true');
						$field->addAttribute('edit', 'true');
						$field->addAttribute('clear', 'true');
						$field->addAttribute('propagate', 'true');
						$field->addAttribute('filter', 'int_array');
						if ($isIncluded == 2) {

							$field->addAttribute('required', 'true');

						}
					}
				}

				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}
}
?>
