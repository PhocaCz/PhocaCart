<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Event\Model\BeforeBatchEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Table\Observer\Tags;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\String\StringHelper;
use Phoca\PhocaCart\Constants\TagType;
use Phoca\PhocaCart\ContentType\ContentTypeHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\I18n\I18nAdminModelTrait;
use Phoca\PhocaCart\I18n\I18nHelper;
use Phoca\PhocaCart\Product\Bundled;

class PhocaCartCpModelPhocaCartItem extends AdminModel
{
    use I18nAdminModelTrait;

	protected	$option 		        = 'com_phocacart';
	protected 	$text_prefix	        = 'com_phocacart';
	public      $typeAlias 		        = 'com_phocacart.phocacartitem';
	protected   $associationsContext    = 'com_phocacart.item';	// ASSOCIATION

    protected $batch_commands = [
        'assetgroup_id'             => 'batchAccess',
        'language_id'               => 'batchLanguage',
        'owner_id'                  => 'batchOwner',
        'tax_id'                    => 'batchTax',
        'catid'                     => 'batchCategory',
        'catid_add'                 => 'batchCategoryAdd',
        'catid_remove'              => 'batchCategoryRemove',
        'group_add'                 => 'batchGroupAdd',
        'group_remove'              => 'batchGroupRemove',
        'manufacturer_id'           => 'batchManufacturer',
        'condition'                 => 'batchCondition',
        'type'                      => 'batchType',
        'stock'                     => 'batchStock',
        'stock_calculation'         => 'batchStockCalculation',
        'min_quantity'              => 'batchMinQuantity',
        'min_multiple_quantity'     => 'batchMinMultipleQuantity',
        'min_quantity_calculation'  => 'batchMinQuantityCalculation',
        'stockstatus_a_id'          => 'batchStockStatusA',
        'stockstatus_n_id'          => 'batchStockStatusN',
        'delivery_date'             => 'batchDeliveryDate',
        'length'                    => 'batchLength',
        'width'                     => 'batchWidth',
        'height'                    => 'batchHeight',
        'weight'                    => 'batchWeight',
        'volume'                    => 'batchVolume',
        'unit_amount'               => 'batchUnitAmount',
        'unit_unit'                 => 'batchUnitUnit',
        'points_needed'             => 'batchPointsNeeded',
        'points_received'           => 'batchPointsReceived',
        'tags_add'                  => 'batchTagsAdd',
        'tags_remove'               => 'batchTagsRemove',
        'taglabels_add'             => 'batchLabelsAdd',
        'taglabels_remove'          => 'batchLabelsRemove',
        'pcf'                       => 'batchFeedOptions',
        'com_fields'                => 'batchCustomFields',
    ];

    public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
    {
        parent::__construct($config, $factory, $formFactory);

        $this->i18nTable = '#__phocacart_products_i18n';
        $this->i18nFields = [
            'title',
            'alias',
            'title_long',
            'description',
            'description_long',
            'features',
            'metatitle',
            'metakey',
            'metadesc',
        ];
    }

    protected function canDelete($record){
		$user = Factory::getUser();

		if (!empty($record->catid)) {
			// catid not used
			return $user->authorise('core.delete', 'com_phocacart.phocacartitem.'.(int) $record->catid);
		} else {
			return parent::canDelete($record);
		}
	}

	protected function canEditState($record) {
		$user = Factory::getUser();

		if (!empty($record->catid)) {
			// catid not used
			return $user->authorise('core.edit.state', 'com_phocacart.phocacartitem.'.(int) $record->catid);
		} else {
			return parent::canEditState($record);
		}
	}

	public function getTable($type = 'PhocaCartItem', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_phocacart.phocacartitem', 'phocacartitem', array('control' => 'jform', 'load_data' => $loadData));
        $form = $this->prepareI18nForm($form);

        $attributeTemplates = ContentTypeHelper::getContentTypes(ContentTypeHelper::Attribute);
        foreach ($attributeTemplates as $attributeTemplate) {
            if ($attributeTemplate->params->get('is_default')) {
                /** @var \Joomla\CMS\Form\Field\SubformField $field */
                $field = $form->getField('attributes');
                $subform = $field->loadSubForm();
                $subform->setFieldAttribute('attribute_template', 'default', $attributeTemplate->id);
                break;
            }
        }
        return $form;
	}

	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartitem.data', array());

		if (is_array($data) && $data) {
			if (!isset($data['group']) || !$data['group']) {
				$data['group'] = PhocacartGroup::getDefaultGroup(1);
			}
		} else {
			$data = $this->getItem();

            $this->loadI18nItem($data);

            $categoryTypes = ContentTypeHelper::getContentTypes(ContentTypeHelper::Category);
            $categories = $data->catid_multiple;
            $data->catid_multiple = [];
            foreach ($categoryTypes as $categoryType) {
                $data->catid_multiple[$categoryType->id] = [];
            }
            foreach ($categories as $catId) {
                $category = PhocacartCategory::getCategoryById($catId);
                $data->catid_multiple[$category->category_type][] = $catId;
            }

            $data->attributes = $this->loadI18nArray($data->attributes, '#__phocacart_attributes_i18n', ['title', 'alias']);
            if ($data->attributes) {
                foreach ($data->attributes as &$attribute) {
                    $attribute['options'] = $this->loadI18nArray($attribute['options'], '#__phocacart_attribute_values_i18n', ['title', 'alias']);
                }
            }
            $data->specifications = $this->loadI18nArray($data->specifications, '#__phocacart_specifications_i18n', ['title', 'alias', 'value', 'alias_value']);
		}

		$this->preprocessData('com_phocacart.phocacartitem', $data);

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

			if (isset($item->gift_types)) {
				$registry = new Registry;
				$registry->loadString($item->gift_types);
				$item->gift_types = $registry->toArray();
			}

			if (isset($item->aidata)) {
				$registry = new Registry;
				$registry->loadString($item->aidata);
				$item->aidata = $registry->toArray();
			}

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


			$item->set('additional_download_files', PhocacartFileAdditional::getProductFilesByProductId((int)$item->id, 2));
			$item->set('additional_images', PhocacartImageAdditional::getImagesByProductId((int)$item->id, 2));

			$attributes = PhocacartAttribute::getAttributesById((int)$item->id, 2, true);
			if (!empty($attributes)) {
				foreach ($attributes as $k => $v) {
					$attributes[$k]['options']	= PhocacartAttribute::getOptionsById((int)$v['id'], 2, true);
				}
			}
			$item->set('attributes', $attributes);

			$item->set('specifications', PhocacartSpecification::getSpecificationsById((int)$item->id, 2));

			$item->set('discounts', PhocacartDiscountProduct::getDiscountsById((int)$item->id, 2));
			$item->set('catid_multiple', PhocacartCategoryMultiple::getCategories((int)$item->id, 1));
			$item->set('tags', PhocacartTag::getTags((int)$item->id, 1));
			$item->set('taglabels', PhocacartTag::getTagLabels((int)$item->id, 1));

			$groups = PhocacartGroup::getGroupsById((int)$item->id, 3, 1);
			if (!$groups)
				$groups = PhocacartGroup::getDefaultGroup(1);
			$item->set('group', $groups);

			$parameters = PhocacartParameter::getAllParameters();
			$itemParameters = [];
			foreach ($parameters as $parameter) {
				$itemParameters[$parameter->id] = PhocacartParameter::getParameterValues((int)$item->id, $parameter->id, 1);
			}
			$item->set('items_parameter', $itemParameters);
            $item->related = PhocacartRelated::getRelatedItemsById((int)$item->id, 3);

			$value = '';
			if ((int)$item->id > 0) {
				$relatedOption	= Bundled::getBundledItemsById((int)$item->id, Bundled::SELECT_COMPLETE);
				if($relatedOption) {
					$i = 0;
					foreach($relatedOption as $v) {
						if ($i > 0) {
							$value .= '[|]';
						}

						$title = PhocacartText::filterValue($v->title, 'text');
						$titleCat = PhocacartText::filterValue($v->categories_title, 'text');

						$value .= (int)$v->id . ':'.$title.' ('.$titleCat.')';
						$i++;
					}
				}
			}
			$item->set('bundles', $value);

			// ASSOCIATION
			// Load associated Phoca Cart items
			if (I18nHelper::associationsEnabled()) {
				$item->associations = array();

				if ($item->id != null){
					$associations = Associations::getAssociations('com_phocacart', '#__phocacart_products', 'com_phocacart.item', $item->id, 'id', 'alias', false);


					foreach ($associations as $tag => $association){
						$item->associations[$tag] = $association->id;
					}
				}
			}
		}

		return $item;
	}


	protected function prepareTable($table) {
		jimport('joomla.filter.output');

		$table->title					= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias					= ApplicationHelper::stringURLSafe($table->alias);
		$table->price 					= PhocacartUtils::replaceCommaWithPoint($table->price);
        $table->price                   = PhocacartText::filterValue($table->price, 'float');
		$table->price_original 			= PhocacartUtils::replaceCommaWithPoint($table->price_original);
        $table->price_original          = PhocacartText::filterValue($table->price_original, 'float');
		$table->length 					= PhocacartUtils::replaceCommaWithPoint($table->length);
		$table->width 					= PhocacartUtils::replaceCommaWithPoint($table->width);
		$table->height 					= PhocacartUtils::replaceCommaWithPoint($table->height);
		$table->weight 					= PhocacartUtils::replaceCommaWithPoint($table->weight);
		$table->volume 					= PhocacartUtils::replaceCommaWithPoint($table->volume);
		$table->tax_id 					= PhocacartUtils::getIntFromString($table->tax_id);
		$table->manufacturer_id			= PhocacartUtils::getIntFromString($table->manufacturer_id);
		$table->stock					= PhocacartUtils::getIntFromString($table->stock);
		$table->min_quantity			= PhocacartUtils::getIntFromString($table->min_quantity);
		$table->min_multiple_quantity	= PhocacartUtils::getIntFromString($table->min_multiple_quantity);
		$table->download_hits			= PhocacartUtils::getIntFromString($table->download_hits);
		$table->points_received			= PhocacartUtils::getIntFromString($table->points_received);
		$table->points_needed			= PhocacartUtils::getIntFromString($table->points_needed);
		$table->unit_amount				= PhocacartUtils::getNullFromEmpty($table->unit_amount);

		if ($table->delivery_date == '0' || $table->delivery_date == '') {
			$table->delivery_date = '0000-00-00 00:00:00';
		}

		if ($table->date_update == '0' || $table->date_update == '') {
			$table->date_update = '0000-00-00 00:00:00';
		}

		if ($table->created == '0' || $table->created == '') {
			$table->created = '0000-00-00 00:00:00';
		}

		if (!isset($table->modified) || $table->modified == '0' || $table->modified == '') {
			$table->modified = '0000-00-00 00:00:00';
		}

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		if (empty($table->redirect_product_id)) {
			$table->redirect_product_id = 0;// null with $table->store(true)
		}

		if (empty($table->redirect_url)) {
			$table->redirect_url = '';// null with $table->store(true)
		}
	}

	public function validate($form, $data, $group = null)
	{
		// only date fields are defined as datetime in DB - causing issues with format in API, need to convert them to date only
		if (isset($data['date']) && is_string($data['date']) && $data['date']) {
			$data['date'] = (new Joomla\CMS\Date\Date($data['date']))->format(Text::_('DATE_FORMAT_FILTER_DATE'));
		}
		if (isset($data['date_update']) && is_string($data['date_update']) && $data['date_update']) {
			$data['date_update'] = (new Joomla\CMS\Date\Date($data['date_update']))->format(Text::_('DATE_FORMAT_FILTER_DATE'));
		}
		return parent::validate($form, $data, $group);
	}

	function save($data) {
        $i18nData   = $this->prepareI18nData($data);

        if ($data['catid_multiple']) {
            $categories = [];
            foreach ($data['catid_multiple'] as $cats) {
                $categories = array_merge($categories, $cats);
            }
            $data['catid_multiple'] = $categories;
        }

		$app		= Factory::getApplication();
		$input  	= Factory::getApplication()->input;

		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

		$user = Factory::getUser();

		// ALIAS
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0)) {
			if ($data['alias'] == null) {
				if (Factory::getConfig()->get('unicodeslugs') == 1) {
					$data['alias'] = OutputFilter::stringURLUnicodeSlug($data['title']);
				} else {
					$data['alias'] = OutputFilter::stringURLSafe($data['title']);
				}


				if ($table->load(array('alias' => $data['alias']))){
					$msg = Text::_('COM_PHOCACART_SAVE_WARNING');
				}

				list($title, $alias) = $this->generateNewTitle(0, $data['alias'], $data['title']);
				$data['alias'] = $alias;

				if (isset($msg)) {
					Factory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		} else if ($table->load(array('alias' => $data['alias'])) && ($table->getId() != $data['id'] || $data['id'] == 0)) {
			$this->setError(Text::_('COM_PHOCACART_ERROR_ITEM_UNIQUE_ALIAS'));
			return false;
		}



		if (!empty($data['feed'])) {
			PluginHelper::importPlugin('pcf');
			$registry 	= new Registry($data['feed']);
			//$registry 	= new JRegistry($dataPh);
			$dataFeed 	= $registry->toString();
			if($dataFeed != '') {
				$data['params_feed'] = $dataFeed;
			}
		} else {
			$data['params_feed'] = '';
		}


		if (!empty($data['gift_types'])) {
			$registry 	= new Registry($data['gift_types']);
			$dataGiftTypes 	= $registry->toString();
			if($dataGiftTypes != '') {
				$data['gift_types'] = $dataGiftTypes;
			}
		} else {
			$data['gift_types'] = '';
		}

		if (!empty($data['aidata'])) {
			$registry 	= new Registry($data['aidata']);
			$aIData 	= $registry->toString();
			if($aIData != '') {
				$data['aidata'] = $aIData;
			}
		} else {
			$data['aidata'] = '';
		}




		// Include the content plugins for the on save events.
		PluginHelper::importPlugin('content');

		// Load the row if saving an existing record.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Set product count for manufacturers (we need to recount obsolete data and even new data - if manufacturer will be changed when saving, we need to recount old data
        $previousManufacturers = array();
		if (!$isNew) {
		    $previousManufacturers = PhocacartManufacturer::getManufacturers($data['id'], 1);
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

		// if new item, order last in appropriate group
		// Not used in multiple mode
		//if (!$table->getId()) {
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

        // Phoca Cart Trigger the before event.
		PluginHelper::importPlugin('pca');
		$result = Dispatcher::dispatch(new Event\Admin\Item\BeforeSave($this->option.'.'.$this->name, $table, $isNew, $data));
		// Store the data.
		if (in_array(false, $result, true) || !$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Joomla Core Trigger the onContentBeforeSave event.
		PluginHelper::importPlugin($this->events_map['save']);
		$result = Dispatcher::dispatchBeforeSave($this->event_before_save, $this->option . '.' . $this->name, $table, $isNew, $data);
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Test Thumbnails (Create if not exists)
		if ($table->image != '') {
			$thumb = PhocacartFileThumbnail::getOrCreateThumbnail($table->image, '', 1, 1, 1, 0, 'productimage');
		}

		if ((int)$table->getId() > 0) {


		    $currentManufacturers = isset($data['manufacturer_id']) && (int)$data['manufacturer_id'] > 0 ? array(0 => (int)$data['manufacturer_id']) : array();
		    $allManufacturers = array_unique(array_merge($previousManufacturers, $currentManufacturers));
			PhocacartCount::setProductCount($allManufacturers, 'manufacturer', 1);// We need to recount all manufacturers - previous (now deleted), and new

			if (!isset($data['catid_multiple'])) {
				$data['catid_multiple'] = array();
			}

			$previousCategories = PhocacartCategoryMultiple::getCategories((int)$table->getId(), 1);
			PhocacartCategoryMultiple::storeCategories($data['catid_multiple'], (int)$table->getId());
			$allCategories = array_unique(array_merge($previousCategories, $data['catid_multiple']));
			PhocacartCount::setProductCount($allCategories, 'category', 1);// We need to recount all categories - previous (now deleted), and new

			if (isset($data['featured'])) {
				$this->featured((int)$table->getId(), $data['featured']);
			}

			PhocacartRelated::storeRelatedItems((int)$table->getId(), $data['related']);

			if (!isset($data['attributes'])) {
				$data['attributes'] = array();
			}
			if (!isset($data['group'])) {
				$data['group'] = array();
			}


			PhocacartFileAdditional::storeProductFilesByProductId((int)$table->getId(), $data['additional_download_files']);
			PhocacartImageAdditional::storeImagesByProductId((int)$table->getId(), $data['additional_images']);
			PhocacartAttribute::storeAttributesById((int)$table->getId(), $data['attributes']);
			PhocacartSpecification::storeSpecificationsById((int)$table->getId(), $data['specifications']);
			PhocacartDiscountProduct::storeDiscountsById((int)$table->getId(), $data['discounts']);

			PhocacartGroup::storeGroupsById((int)$table->getId(), 3, $data['group']);
			PhocacartPriceHistory::storePriceHistoryById((int)$table->getId(), $data['price']);
			PhocacartGroup::updateGroupProductPriceById((int)$table->getId(), $data['price']);
			PhocacartGroup::updateGroupProductRewardPointsById((int)$table->getId(), $data['points_received']);


			// UPDATE this file too:
			// administrator\components\com_phocacart\libraries\phocacart\product\product.php storeProduct() function

			// TAGS
			if (!isset($data['tags'])) {
				$data['tags'] = array();
			}

			$previousTags = PhocacartTag::getTags((int)$table->getId(), 1);
			PhocacartTag::storeTags($data['tags'], (int)$table->getId());
			$allTags = array_unique(array_merge($previousTags, $data['tags']));
			PhocacartCount::setProductCount($allTags, 'tag', 1);// We need to update product count even for values which were removed when editing ($allTags)

			// TAG LABELS
			if (!isset($data['taglabels'])) {
				$data['taglabels'] = array();
			}

			$previousLabels = PhocacartTag::getTagLabels((int)$table->getId(), 1);


			PhocacartTag::storeTagLabels($data['taglabels'], (int)$table->getId());
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

						$previousParameterValues = PhocacartParameter::getParameterValues((int)$table->getId(), $idP, 1);
						PhocacartParameter::storeParameterValues($data['items_parameter'][$idP], (int)$table->getId(), $idP);
						$allParameterValues = array_unique(array_merge($previousParameterValues, $data['items_parameter'][$idP]));
						PhocacartCount::setProductCount($allParameterValues, 'parameter', 1);
					}
				}
			}
		}

        $this->saveI18nData($table->getId(), $i18nData);
        $this->cleanCache();


        // Phoca Cart Trigger the after save event.
		Dispatcher::dispatch(new Event\Admin\Item\AfterSave($this->option.'.'.$this->name, $table, $isNew, $data));

        // Joomla Core Trigger the onContentAfterSave event. CUSTOM FIELDS
		Dispatcher::dispatchAfterSave($this->event_after_save, $this->option . '.' . $this->name, $table, $isNew, $data);

		$pkName = $table->getKeyName();
		if (isset($table->$pkName)) {
			// need to be here to initialize populatestate
			$this->getState();
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);



		// ASSOCIATION
        if ($this->associationsContext && I18nHelper::associationsEnabled() && !empty($data['associations'])) {
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

	public function delete(&$pks = [])
    {
        ArrayHelper::toInteger($pks);
        $cids = implode(',', $pks);

        // Get all manufacturers from products which should be removed so we can update count of products for manufacturers
        $allManufacturers = PhocacartManufacturer::getManufacturersByIds($cids);

        $table = $this->getTable();
        if (!$this->canDelete($table)) {
            $error = $this->getError();
            if ($error) {
                Log::add($error, Log::WARNING);

                return false;
            }
            else {
                Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING);

                return false;
            }
        }

        // Find all downloadable files to remove them
        $foldersP = PhocacartDownload::getProductDownloadFolderByProducts($pks);
        $foldersA = PhocacartDownload::getAttributeOptionDownloadFolderByProducts($pks);
        // Will be deleted at the bottom if everything is OK


        // 1. DELETE ITEMS
       /* $query = 'DELETE FROM #__phocacart_products'
            . ' WHERE id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();*/

        $app		= Factory::getApplication();
        PluginHelper::importPlugin($this->events_map['delete']);
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if ($this->canDelete($table)) {
                    if (!$table->delete($pk)) {
                        throw new Exception($table->getError(), 500);
                        return false;
                    }
                    $app->triggerEvent($this->event_after_delete, array($this->option.'.'.$this->name, $table));
                }
            }
        }

        // 2. DELETE ATTRIBUTE OPTIONS
        $query = 'SELECT id FROM #__phocacart_attributes WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $attrOptions = $this->_db->loadAssocList();
        $attrArray   = array();
        if (!empty($attrOptions)) {
            foreach ($attrOptions as $k => $v) {
                $attrArray[] = $v['id'];
            }
            if (!empty($attrArray)) {
                $attrs = implode(',', $attrArray);
                $query = 'DELETE FROM #__phocacart_attribute_values'
                    . ' WHERE attribute_id IN ( ' . $attrs . ' )';
                $this->_db->setQuery($query);
                $this->_db->execute();
            }
        }

        // 3. DELETE ATTRIBUTES
        $query = 'DELETE FROM #__phocacart_attributes'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();


        // 4. DELETE RELATED
        $query = 'DELETE FROM #__phocacart_product_related'
            . ' WHERE product_a IN ( ' . $cids . ' ) OR product_b IN (' . $cids . ')';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // 4a. DELETE BUNDLED
        $query = 'DELETE FROM #__phocacart_product_bundles'
            . ' WHERE main_product_id IN ( ' . $cids . ' ) OR child_product_id IN (' . $cids . ')';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // 5. DELETE FEATURED
        $query = 'DELETE FROM #__phocacart_product_featured'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();
        $tableF = $this->getTable('PhocacartFeatured', 'Table');
        $tableF->reorder();

        // 6. DELETE IMAGES
        $query = 'DELETE FROM #__phocacart_product_images'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // 7. DELETE REVIEWS
        $query = 'DELETE FROM #__phocacart_reviews'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // 8. DELETE CATEGORY RELATIONSHIP
        $allCategories = PhocacartCategoryMultiple::getCategoriesByIds($cids);
        $query         = 'DELETE FROM #__phocacart_product_categories'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();
        PhocacartCount::setProductCount($allCategories, 'category', 1);

        // 9. DELETE SPECIFICATIONS
        $query = 'DELETE FROM #__phocacart_specifications'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // 9. DELETE PRODUCT DISCOUNTS
        $query = 'DELETE FROM #__phocacart_product_discounts'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // 9. DELETE PRODUCT CUSTOMER GROUPS
        $query = 'DELETE FROM #__phocacart_item_groups'
            . ' WHERE item_id IN ( ' . $cids . ' )'
            . ' AND type = 3';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // 10. DELETE PRODUCT PRICE CUSTOMER GROUPS
        $query = 'DELETE FROM #__phocacart_product_price_groups'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();


        // 11. DELETE PRODUCT POINT CUSTOMER GROUPS
        $query = 'DELETE FROM #__phocacart_product_point_groups'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // 12. DELETE PRODUCT ADDITIONAL FILES
        $query = 'DELETE FROM #__phocacart_product_files'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();


        // 12. DELETE PRODUCT TAGS
        $allTags = PhocacartTag::getTagsByIds($cids);// All these tags will be influneced by deleting the item, so we need to recount the products for tags then
        $query   = 'DELETE FROM #__phocacart_tags_related'
            . ' WHERE item_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();
        PhocacartCount::setProductCount($allTags, 'tag', 1);

        // 13. DELETE PRODUCT LABELS
        $allLabels = PhocacartTag::getTagsLabelsByIds($cids);
        $query     = 'DELETE FROM #__phocacart_taglabels_related'
            . ' WHERE item_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();
        PhocacartCount::setProductCount($allLabels, 'label', 1);

        // 14. DELETE PRODUCT PARAMTERS
        $allParameterValues = PhocacartParameter::getParameterValuesByIds($cids);
        $query              = 'DELETE FROM #__phocacart_parameter_values_related'
            . ' WHERE item_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();
        PhocacartCount::setProductCount($allParameterValues, 'parameter', 1);

        // Recount all manufacturers which will be removed (after removing) so the count will be updated
        PhocacartCount::setProductCount($allManufacturers, 'manufacturer', 1);

        // 16. DELETE ADVANCED STOCK ITEMS
        $query = 'DELETE FROM #__phocacart_product_stock'
            . ' WHERE product_id IN ( ' . $cids . ' )';
        $this->_db->setQuery($query);
        $this->_db->execute();

        // Remove download folders
        PhocacartFile::deleteDownloadFolders($foldersP, 'productfile');
        PhocacartFile::deleteDownloadFolders($foldersA, 'attributefile');

        $this->deleteI18nData($pks);

        return true;
    }

	protected function batchCopy($value, $pks, $contexts)
	{
		// Destination Category
		$categoryId	= (int) $value;
		// Source Category (current category)
        $app 			= Factory::getApplication('administrator');
        $batchParams 	= $app->input->post->get('batch', array(), 'array');


        $table	= $this->getTable();

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

		if (empty($categoryId)) {
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
			$table->published = 0;// As default the copied new product is unpublished

			// Reset the ID because we are making a copy
			$table->id		= 0;

			// New category ID
		//	$table->catid	= $categoryId;

			// Ordering
		//	$table->ordering = $this->increaseOrdering($categoryId);

			$table->hits = 0;


			$params['olddownloadfolder']		= $table->download_folder;
			$params['newdownloadfolder']		= $params['olddownloadfolder'];

			// COPY OR BATCH functions - we cannot do the same tokens so create new token and token folder and if set copy the files
			// EACH DOWNLOAD FILE MUST HAVE UNIQUE DOWNLOAD TOKEN AND DOWNLOAD FOLDER
			$copy = 1;// When copying attributes or batch products we do a copy of attributes (copy = 1) but in this case without copying download files on the server
			if (isset($batchParams['copy_download_files']) && $batchParams['copy_download_files'] == 1) {
				$copy = 2;// The same like 1 but in this case we even copy the download files on the server
			}
			if ($copy > 0) {

                $pathFile = PhocacartPath::getPath('productfile');

                // First create new token and token folder
				$table->download_token 			= PhocacartUtils::getToken();
				$table->download_folder			= PhocacartUtils::getAndCheckToken('folder', $pathFile);
                //$table->download_folder			= PhocacartUtils::getToken('folder');
				$params['newdownloadfolder']	= $table->download_folder;



				if($copy == 2) {

					// Download File
					if ($table->download_file != '' && File::exists($pathFile['orig_abs_ds'] . $table->download_file)) {

						$newDownloadFile = str_replace($params['olddownloadfolder'], $table->download_folder, $table->download_file);
						if (!Folder::create($pathFile['orig_abs_ds'] . $table->download_folder)) {
							// Error message will be set below: COM_PHOCACART_ERROR_DOWNLOAD_FILE_OF_ATTRIBUTE_OPTION_DOES_NOT_EXIST

							$msg = Text::_('COM_PHOCACART_DOWNLOAD_FOLDER') . ': ' . $table->download_folder . "<br />";
							$msg .= Text::_('COM_PHOCACART_ERROR_DOWNLOAD_FOLDER_NOT_CREATED');
							$app->enqueueMessage($msg, 'error');
						}

						if (!File::copy($pathFile['orig_abs_ds'] . $table->download_file, $pathFile['orig_abs_ds'] . $newDownloadFile)) {
							$msg = Text::_('COM_PHOCACART_DOWNLOAD_FILE') . ': ' . $table->download_file . "<br />";
							$msg .= Text::_('COM_PHOCACART_ERROR_DOWNLOAD_FILE_NOT_COPIED');
							$app->enqueueMessage($msg, 'error');
						}
						$table->download_file = $newDownloadFile;
					}

					// Additional Download Files
					$downloadFiles = PhocacartFileAdditional::getProductFilesByProductId($pk);

					if(!empty($downloadFiles)) {
						foreach($downloadFiles as $k => $v) {

							if (isset($v['download_file']) && $v['download_file'] != '') {
								$newDownloadFile = str_replace($params['olddownloadfolder'], $table->download_folder, $v['download_file']);

								// In case download_file is emtpy we schould create the folder
								if (!Folder::exists($pathFile['orig_abs_ds'] . $table->download_folder)) {
									if (!Folder::create($pathFile['orig_abs_ds'] . $table->download_folder)) {
										// Error message will be set below: COM_PHOCACART_ERROR_DOWNLOAD_FILE_OF_ATTRIBUTE_OPTION_DOES_NOT_EXIST

										$msg = Text::_('COM_PHOCACART_DOWNLOAD_FOLDER') . ': ' . $table->download_folder . "<br />";
										$msg .= Text::_('COM_PHOCACART_ERROR_DOWNLOAD_FOLDER_NOT_CREATED');
										$app->enqueueMessage($msg, 'error');
									}
								}
								if (!File::copy($pathFile['orig_abs_ds'] . $v['download_file'], $pathFile['orig_abs_ds'] . $newDownloadFile)) {
									$msg = Text::_('COM_PHOCACART_DOWNLOAD_FILE') . ': ' . $table->download_file . "<br />";
									$msg .= Text::_('COM_PHOCACART_ERROR_DOWNLOAD_FILE_NOT_COPIED');
									$app->enqueueMessage($msg, 'error');
								}
							}
						}
					}
					// Files copied on server, we will copy the database info below in PhocacartUtilsBatchhelper::storeProductItems

				} else {
					$table->download_file = '';
				}

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

			$newId = $table->get('id');



			// Add the new ID to the array
			$newIds[$pk]	= $newId;

			// Store other new information
			PhocacartUtilsBatchhelper::storeProductItems($pk, (int)$newId, $batchParams, $params);
			$dataCat[]		= (int)$categoryId;// categoryId - the category where we want to copy the products



			// Copy all source categories
			if (isset($batchParams['copy_all_cats']) && $batchParams['copy_all_cats'] == 1) {
				$currentDataCat = PhocacartCategoryMultiple::getAllCategoriesByProduct((int)$pk);// plus all other categories of this product
																							 // will be copied too
				 // 1) Bind categories - destination category + all categories from source product (source product -> destination product)
				$dataCat2		= array_merge($dataCat, $currentDataCat);
			} else {
				$dataCat2		= $dataCat;
			}


			// 2) Remove duplicates
			$dataCat2		= array_unique($dataCat2);



		/*
		 * 	Yes when copying - we duplicate the item intentionally
		 * 	// 3) Remove the source category - we copy product from source category and the product is included in source category
			//    so don't copy it again to not get duplicates in the same category*/
			//
			//
			// $currentCatidA 	= array(0 => (int)$currentCatid);
			// $dataCat2 		= array_diff($dataCat2, $currentCatidA);

			// PhocacartCategoryMultiple::storeCategories function does not delete current category, so if we have removed it from array,
			// we need to delete it from database too
			// PhocacartCategoryMultiple::deleteCategoriesFromProduct($currentCatidA, (int)$newId);
			// This is based on filtering in administration - if we display products from category A, the A is $currentCatidA



			// The following function does not delete source categories, it only adds new so source categories needs to be deleted
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
	 * @return  bool  True if successful, false otherwise and internal error is set.
	 *
	 * @since	11.1
	 */
	protected function batchMove($value, $pks, $contexts)
	{
		$categoryId	= (int) $value;

		$table	= $this->getTable();

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

		if (empty($categoryId)) {
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

			PhocacartCategoryMultiple::storeCategories($dataCat, (int)$table->getId());
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}


	function recreate($cid = array(), &$message = '') {

		if (count( $cid )) {
			ArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'SELECT a.id, a.image, a.title'.
					' FROM #__phocacart_products AS a' .
					' WHERE a.id IN ( '.$cids.' )';
			$this->_db->setQuery($query);
			$files = $this->_db->loadObjectList();
			if (isset($files) && count($files)) {
				$msg = array();
				foreach($files as $k => $v) {
					$title	= Text::_('COM_PHOCACART_PRODUCT') . ' ' . $v->title . ': ';

					if (isset($v->image) && $v->image != '') {

						$original	= PhocacartFile::existsFileOriginal($v->image, 'productimage');
						if (!$original) {
							// Original does not exist - cannot generate new thumbnail
							$msg[$k] = $title . Text::_('COM_PHOCACART_FILEORIGINAL_NOT_EXISTS');
							//return false;
							continue;
						}

						// Delete old thumbnails
						$deleteThubms = PhocacartFileThumbnail::deleteFileThumbnail($v->image, 1, 1, 1, 'productimage');
						if (!$deleteThubms) {
							$msg[$k] = $title . Text::_('COM_PHOCACART_ERROR_DELETE_THUMBNAIL');
							//return false;
							continue;
						}
						$createThubms = PhocacartFileThumbnail::getOrCreateThumbnail($v->image, 0, 1,1,1,0,'productimage');
						if (!$createThubms) {
							$msg[$k] = $title . Text::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');
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
										//$message = Text::_('COM_PHOCACART_FILEORIGINAL_NOT_EXISTS');
										//return false;
										continue;
									}

									// Delete old thumbnails
									$deleteThubms2 = PhocacartFileThumbnail::deleteFileThumbnail($v2->image, 1, 1, 1, 'productimage');
									if (!$deleteThubms2) {
										//$message = Text::_('COM_PHOCACART_ERROR_DELETE_THUMBNAIL');
										//return false;
										continue;
									}
									$createThubms2 = PhocacartFileThumbnail::getOrCreateThumbnail($v2->image, 0, 1,1,1,0,'productimage');
									if (!$createThubms2) {
										//$message = Text::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');
										//return false;
										continue;
									}

								}
							}
						}

						// Attribute images
						if (isset($v->id) && (int)$v->id > 0) {
							$query = 'SELECT p.image, p.id'.
									' FROM #__phocacart_attribute_values AS p' .
									' INNER JOIN #__phocacart_attributes as a ON a.id = p.attribute_id' .
									' WHERE a.product_id ='.(int)$v->id;
							$this->_db->setQuery($query);
							$files3 = $this->_db->loadObjectList();
							if (isset($files3) && count($files3)) {
								foreach($files3 as $k3 => $v3) {

									$original3	= PhocacartFile::existsFileOriginal($v3->image, 'productimage');
									if (!$original3) {
										// Original does not exist - cannot generate new thumbnail
										//$message = Text::_('COM_PHOCACART_FILEORIGINAL_NOT_EXISTS');
										//return false;
										continue;
									}

									// Delete old thumbnails
									$deleteThubms3 = PhocacartFileThumbnail::deleteFileThumbnail($v3->image, 1, 1, 1, 'productimage');
									if (!$deleteThubms3) {
										//$message = Text::_('COM_PHOCACART_ERROR_DELETE_THUMBNAIL');
										//return false;
										continue;
									}
									$createThubms3 = PhocacartFileThumbnail::getOrCreateThumbnail($v3->image, 0, 1,1,1,0,'productimage');
									if (!$createThubms3) {
										//$message = Text::_('COM_PHOCACART_ERROR_WHILECREATINGTHUMB');
										//return false;
										continue;
									}

								}
							}
						}


					} else {
						$msg[$k] = $title . Text::_('COM_PHOCACART_FILENAME_NOT_EXISTS');
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

	public function featured($pks, $value = 0) {
		// Sanitize the ids.
		$pks = (array) $pks;
		ArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(Text::_('COM_PHOCACART_NO_ITEM_SELECTED'));
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
		$table 	= $this->getTable('PhocacartProductCategories', 'Table');

		// CURRENT CATEGORY
		$app 			= Factory::getApplication();
		/*$filter 	= $app->input->post->get('filter', array(), 'array');

		$currentCatid = 0;
		if (isset($filter['category_id'])) {
			$currentCatid = (int)$filter['category_id'];
		}*/
		// Joomla BUG: https://github.com/joomla/joomla-cms/issues/36346 $this->t['catid']
        // Add catid to the URL instead of sending in POST
        // administrator/components/com_phocacart/views/phocacartitems/tmpl/default.php 37

		$catid 	= $app->input->get('catid', 0, 'int');

		$currentCatid = 0;
		if ((int)$catid > 0) {
			$currentCatid = (int)$catid;
		}

		$tableClassName = get_class($table);
		$contentType = new UCMType;
		$type = $contentType->getTypeByTable($tableClassName);
		//$tagsObserver = $table->getObserverOfClass('Tags');
		$conditions = array();


		if (empty($pks))
		{
			return $app->enqueueMessage(Text::_($this->text_prefix.'_ERROR_NO_ITEMS_SELECTED'), 'error');

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
				Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING);
			}
			elseif ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];


				if ($type)
				{
					//$this->createTagsHelper($tagsObserver, $type, $pk, $type->type_alias, $table);
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
                    //$conditions[] = array($table->$pkName, $condition);
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

		$app 			= Factory::getApplication('administrator');
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

	public function copyattributes(&$cid = array(), $idSource = 0)
    {
		$app 							= Factory::getApplication();
		$copy_attributes_download_files 	= $app->input->post->get('copy_attributes_download_files', 0, 'int');

		$copy = 1;// When copying attributes or batch products we do a copy of attributes (copy = 1) but in this case without copying download files on the server
		if ($copy_attributes_download_files == 1) {
			$copy = 2;// The same like 1 but in this case we even copy the download files on the server, see: PhocacartAttribute::storeAttributesById() for more info
		}
		$cA = 0;

		if (count( $cid ) && (int)$idSource > 0) {
			ArrayHelper::toInteger($cid);

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
				$app->enqueueMessage(Text::_('COM_PHOCACART_SELECTED_SOURCE_PRODUCT_DOES_NOT_HAVE_ANY_ATTRIBUTES'), 'error');
				return false;
			}

			if (!empty($aA)) {
				foreach($cid as $k => $v) {

					if ((int)$v != $idSource) { // Do not copy to itself




						PhocacartAttribute::storeAttributesById((int)$v, $aA, 1, $copy);
						$cA++;
					}
				}
			}

		}

		if ($cA > 0) {
			return true;
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_NO_ATTRIBUTE_COPIED'), 'error');
			return false;
		}
	}

	// ASSOCIATION/PARAMETERS
	protected function preprocessForm(Form $form, $data, $group = 'content')
    {
		// Association Phoca Cart items
		if (I18nHelper::associationsEnabled()){
			$languages = LanguageHelper::getContentLanguages(false, true, null, 'ordering', 'asc');

			if (count($languages) > 1){
				$addform = new SimpleXMLElement('<form />');
				$fields = $addform->addChild('fields');
				$fields->addAttribute('name', 'associations');
				$fieldset = $fields->addChild('fieldset');
				$fieldset->addAttribute('name', 'item_associations');
				$fieldset->addAttribute('addfieldprefix', 'Joomla\Component\Menus\Administrator\Field');


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
					//$option = $field->addChild('option', 'COM_MENUS_ITEM_FIELD_ASSOCIATION_NO_VALUE');
					//$option->addAttribute('value', '');
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
				$field->addAttribute('formsource', 'plugins/pcf/'.$element.'/models/forms/item.xml');
				$field->addAttribute('clear', 'true');
				$field->addAttribute('propagate', 'true');
				$form->load($addformF, false);
			}
		}

		// Load Parameter Values for Parameters
		$parameters = PhocacartParameter::getAllParameters();

		if (count($parameters) > 0){
			$addform = new SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'items_parameter');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'items_parameter');

			foreach ($parameters as $v)
			{
				$field = $fieldset->addChild('field');
				$field->addAttribute('name', $v->id);
				$field->addAttribute('parameterid', $v->id);
				$field->addAttribute('type', 'PhocaCartParameterValues');
				$field->addAttribute('label', $v->title);
				$field->addAttribute('multiple', 'true');
				$field->addAttribute('translate_label', 'false');
				$field->addAttribute('select', 'true');
				$field->addAttribute('new', 'true');
				$field->addAttribute('edit', 'true');
				$field->addAttribute('clear', 'true');
				$field->addAttribute('propagate', 'true');
				$field->addAttribute('layout', 'joomla.form.field.list-fancy-select');
			}

			$form->load($addform, false);
		}

		if (Factory::getApplication()->isClient('api')) {
			$form->setFieldAttribute('catid_multiple', 'required', 'false');
		}

		parent::preprocessForm($form, $data, $group);
	}

	public function batch($commands, $pks, $contexts)
	{
        $input = Factory::getApplication()->getInput();
        $itemsFilter = isset($commands['items_filter']) ? $commands['items_filter'] : 'selected';
		if (empty($pks) && in_array($itemsFilter, ['filtered', 'all'])) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select('DISTINCT a.id')
                ->from('`#__phocacart_products` AS a');

            if ($itemsFilter == 'filtered') {
                $filter = new Registry($input->get('filter', [], 'raw'));

                $paramsC                      = PhocacartUtils::getComponentParameters();
                $search_matching_option_admin = $paramsC->get('search_matching_option_admin', 'exact');

                // Filter by access level
                $access = $filter->get('access');
                if ($access) {
                    $query->where('a.access = ' . (int) $access);
                }

                // Filter by published state.
                $published = $filter->get('published', '');
                if (is_numeric($published)) {
                    $query->where('a.published = ' . (int) $published);
                }
                else if ($published === '') {
                    $query->where('(a.published IN (0, 1))');
                }

                // When category is selected, we need to get info about selected category
                // When it is not selected, don't ask for it to make the query faster
                // pc.ordering is set as default ordering and it can be set (even igonered) even whey category not selected
                // is complicated but loads much faster

                // Filter by category.
                $categoryId = $filter->get('category_id');
                if (is_numeric($categoryId)) {
                    $query->join('LEFT', '#__phocacart_product_categories AS pc ON a.id = pc.product_id');
                    $query->where('pc.category_id = ' . (int) $categoryId);
                }

                // Filter by manufacturer
                $manufacturerId = $filter->get('manufacturer_id');
                if (is_numeric($manufacturerId)) {
                    $query->where('a.manufacturer_id = ' . (int) $manufacturerId);
                }

                // Filter by owner
                $ownerId = $filter->get('owner_id');
                if (is_numeric($ownerId)) {
                    $query->where('a.owner_id = ' . (int) $ownerId);
                }

                // Filter by stock
                $inStock = $filter->get('instock');
                if (is_numeric($inStock)) {
                    if ($inStock) {
                        $query->where('a.stock > 0');
                    }
                    else {
                        $query->where('a.stock <= 0');
                    }
                }

                // Filter by language.
                if ($language = $filter->get('language')) {
                    $query->where('a.language = ' . $db->quote($language));
                }

                // Filter by search in title
                $search = $filter->get('search');
                if (!empty($search)) {
                    if (stripos($search, 'id:') === 0) {
                        $query->where('a.id = ' . (int) substr($search, 3));
                    }
                    else {
                        switch ($search_matching_option_admin) {
                            case 'all':
                            case 'any':
                                $words  = explode(' ', $search);
                                $wheres = array();
                                foreach ($words as $word) {
                                    if (!$word = trim($word)) {
                                        continue;
                                    }

                                    $word        = $db->quote('%' . $db->escape($word, true) . '%', false);
                                    $wheresSub   = array();
                                    $wheresSub[] = 'a.title LIKE ' . $word;
                                    $wheresSub[] = 'a.alias LIKE ' . $word;
                                    $wheresSub[] = 'a.metakey LIKE ' . $word;
                                    $wheresSub[] = 'a.metadesc LIKE ' . $word;
                                    $wheresSub[] = 'a.description LIKE ' . $word;
                                    $wheresSub[] = 'a.sku LIKE ' . $word;
                                    $wheresSub[] = 'a.ean LIKE ' . $word;
                                    $wheresSub[] = 'exists (select ps.id from #__phocacart_product_stock AS ps WHERE a.id = ps.product_id AND ps.sku LIKE ' . $word . ' OR ps.ean LIKE ' . $word . ') ';
                                    $wheres[]    = implode(' OR ', $wheresSub);
                                }

                                $query->where('((' . implode(($search_matching_option_admin == 'all' ? ') AND (' : ') OR ('), $wheres) . '))');

                                break;

                            case 'exact':
                            default:
                                $text        = $db->quote('%' . $db->escape($search, true) . '%', false);
                                $wheresSub   = array();
                                $wheresSub[] = 'a.title LIKE ' . $text;
                                $wheresSub[] = 'a.alias LIKE ' . $text;
                                $wheresSub[] = 'a.metakey LIKE ' . $text;
                                $wheresSub[] = 'a.metadesc LIKE ' . $text;
                                $wheresSub[] = 'a.description LIKE ' . $text;
                                $wheresSub[] = 'a.sku LIKE ' . $text;
                                $wheresSub[] = 'a.ean LIKE ' . $text;
                                $wheresSub[] = 'exists (select ps.id from #__phocacart_product_stock AS ps WHERE a.id = ps.product_id AND ps.sku LIKE ' . $text . ' OR ps.ean LIKE ' . $text . ') ';
                                $query->where('((' . implode(') OR (', $wheresSub) . '))');

                                break;
                        }
                    }
                }
            }

            $db->setQuery($query);
            $pks = $db->loadColumn();
		}

		return parent::batch($commands, $pks, $contexts);
	}

    private function batchDBField(string $fieldname, $value, $pks, $contexts): bool
    {
        $this->initBatch();

        foreach ($pks as $pk) {
            if ($this->user->authorise('core.edit', $contexts[$pk])) {
                $this->table->reset();
                $this->table->load($pk);
                $this->table->$fieldname = $value;

                $event = new BeforeBatchEvent(
                    $this->event_before_batch,
                    ['src' => $this->table, 'type' => $fieldname]
                );
                $this->dispatchEvent($event);

                // Check the row.
                if (!$this->table->check()) {
                    $this->setError($this->table->getError());

                    return false;
                }

                if (!$this->table->store()) {
                    $this->setError($this->table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    protected function batchOwner($value, $pks, $contexts): bool
    {
        return $this->batchDBField('owner_id', (int)$value, $pks, $contexts);
    }

    protected function batchTax($value, $pks, $contexts): bool
    {
        return $this->batchDBField('tax_id', (int)$value, $pks, $contexts);
    }

    protected function batchCategory($value, $pks, $contexts): bool
    {
        return $this->batchDBField('catid', (int)$value, $pks, $contexts);
    }

    protected function batchManufacturer($value, $pks, $contexts): bool
    {
        return $this->batchDBField('manufacturer_id', (int)$value, $pks, $contexts);
    }

    protected function batchCondition($value, $pks, $contexts): bool
    {
        return $this->batchDBField('condition', (int)$value, $pks, $contexts);
    }

    protected function batchType($value, $pks, $contexts): bool
    {
        return $this->batchDBField('type', (int)$value, $pks, $contexts);
    }

    protected function batchStock($value, $pks, $contexts): bool
    {
        return $this->batchDBField('stock', (int)$value, $pks, $contexts);
    }

    protected function batchStockCalculation($value, $pks, $contexts): bool
    {
        return $this->batchDBField('stock_calculation', (int)$value, $pks, $contexts);
    }

    protected function batchMinQuantity($value, $pks, $contexts): bool
    {
        return $this->batchDBField('min_quantity', (int)$value, $pks, $contexts);
    }

    protected function batchMinMultipleQuantity($value, $pks, $contexts): bool
    {
        return $this->batchDBField('min_multiple_quantity', (int)$value, $pks, $contexts);
    }

    protected function batchMinQuantityCalculation($value, $pks, $contexts): bool
    {
        return $this->batchDBField('min_quantity_calculation', (int)$value, $pks, $contexts);
    }

    protected function batchStockStatusA($value, $pks, $contexts): bool
    {
        return $this->batchDBField('stockstatus_a_id', (int)$value, $pks, $contexts);
    }

    protected function batchStockStatusN($value, $pks, $contexts): bool
    {
        return $this->batchDBField('stockstatus_n_id', (int)$value, $pks, $contexts);
    }

    protected function batchDeliveryDate($value, $pks, $contexts): bool
    {
        return $this->batchDBField('delivery_date', $value, $pks, $contexts);
    }

    protected function batchLength($value, $pks, $contexts): bool
    {
        return $this->batchDBField('length', (float)$value, $pks, $contexts);
    }

    protected function batchWidth($value, $pks, $contexts): bool
    {
        return $this->batchDBField('width', (float)$value, $pks, $contexts);
    }

    protected function batchHeight($value, $pks, $contexts): bool
    {
        return $this->batchDBField('height', (float)$value, $pks, $contexts);
    }

    protected function batchWeight($value, $pks, $contexts): bool
    {
        return $this->batchDBField('weight', (float)$value, $pks, $contexts);
    }

    protected function batchVolume($value, $pks, $contexts): bool
    {
        return $this->batchDBField('volume', (float)$value, $pks, $contexts);
    }

    protected function batchUnitAmount($value, $pks, $contexts): bool
    {
        return $this->batchDBField('unit_amount', (float)$value, $pks, $contexts);
    }

    protected function batchUnitUnit($value, $pks, $contexts): bool
    {
        return $this->batchDBField('unit_unit', $value, $pks, $contexts);
    }

    protected function batchPointsNeeded($value, $pks, $contexts): bool
    {
        return $this->batchDBField('points_needed', (int)$value, $pks, $contexts);
    }

    protected function batchPointsReceived($value, $pks, $contexts): bool
    {
        return $this->batchDBField('points_received', (int)$value, $pks, $contexts);
    }

    private function batchCategories(bool $add, array $value, $pks, $contexts): bool
    {
        $this->initBatch();

        foreach ($pks as $pk) {
            if ($this->user->authorise('core.edit', $contexts[$pk])) {
                $this->table->reset();
                $this->table->load($pk);

                $event = new BeforeBatchEvent(
                    $this->event_before_batch,
                    ['src' => $this->table, 'type' => 'categories']
                );
                $this->dispatchEvent($event);

                // Check the row.
                if (!$this->table->check()) {
                    $this->setError($this->table->getError());

                    return false;
                }

                $categories = PhocacartCategoryMultiple::getCategories((int)$pk, 1);
                $allCategories = array_unique(array_merge($categories, $value));
                if ($add) {
                    $categories = $allCategories;
                } else {
                    $categories = array_diff($categories, $value);
                }
                PhocacartCategoryMultiple::storeCategories($categories, (int)$pk);
                PhocacartCount::setProductCount($allCategories, 'category', 1);
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    protected function batchCategoryAdd($value, $pks, $contexts): bool
    {
        return $this->batchCategories(true, (array)$value, $pks, $contexts);
    }

    protected function batchCategoryRemove($value, $pks, $contexts): bool
    {
        return $this->batchCategories(false, (array)$value, $pks, $contexts);
    }

    private function batchGroup(bool $add, array $value, $pks, $contexts): bool
    {
        $this->initBatch();

        foreach ($pks as $pk) {
            if ($this->user->authorise('core.edit', $contexts[$pk])) {
                $this->table->reset();
                $this->table->load($pk);

                $event = new BeforeBatchEvent(
                    $this->event_before_batch,
                    ['src' => $this->table, 'type' => 'groups']
                );
                $this->dispatchEvent($event);

                // Check the row.
                if (!$this->table->check()) {
                    $this->setError($this->table->getError());

                    return false;
                }

                $groups = PhocacartGroup::getGroupsById((int)$pk, 3, 1);
                if ($add) {
                    $groups = array_unique(array_merge($groups, $value));
                } else {
                    $groups = array_diff($groups, $value);
                }
                PhocacartGroup::storeGroupsById((int)$pk, 3, $groups);
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    protected function batchGroupAdd($value, $pks, $contexts): bool
    {
        return $this->batchGroup(true, (array)$value, $pks, $contexts);
    }

    protected function batchGroupRemove($value, $pks, $contexts): bool
    {
        return $this->batchGroup(false, (array)$value, $pks, $contexts);
    }

    private function batchTags(int $tagsType, bool $add, array $value, $pks, $contexts): bool
    {
        $this->initBatch();

        foreach ($pks as $pk) {
            if ($this->user->authorise('core.edit', $contexts[$pk])) {
                $this->table->reset();
                $this->table->load($pk);

                $event = new BeforeBatchEvent(
                    $this->event_before_batch,
                    ['src' => $this->table, 'type' => $tagsType == TagType::Tag ? 'tags' : 'labels']
                );
                $this->dispatchEvent($event);

                // Check the row.
                if (!$this->table->check()) {
                    $this->setError($this->table->getError());

                    return false;
                }

                $tags = PhocacartTag::getTags((int)$pk, 1);
                $allTags = array_unique(array_merge($tags, $value));
                if ($add) {
                    $tags = $allTags;
                } else {
                    $tags = array_diff($tags, $value);
                }
                if ($tagsType == TagType::Tag) {
                    PhocacartTag::storeTags($tags, (int)$pk);
                    PhocacartCount::setProductCount($allTags, 'tag', 1);
                } else {
                    PhocacartTag::storeTagLabels($tags, (int)$pk);
                    PhocacartCount::setProductCount($allTags, 'label', 1);
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    protected function batchTagsAdd($value, $pks, $contexts): bool
    {
        return $this->batchTags(TagType::Tag, true, (array)$value, $pks, $contexts);
    }

    protected function batchTagsRemove($value, $pks, $contexts): bool
    {
        return $this->batchTags(TagType::Tag, false, (array)$value, $pks, $contexts);
    }

    protected function batchLabelsAdd($value, $pks, $contexts): bool
    {
        return $this->batchTags(TagType::Label, true, (array)$value, $pks, $contexts);
    }

    protected function batchLabelsRemove($value, $pks, $contexts): bool
    {
        return $this->batchTags(TagType::Label, false, (array)$value, $pks, $contexts);
    }

    protected function batchFeedOptions($newValues, $pks, $contexts): bool
    {
        $this->initBatch();

        foreach ($pks as $pk) {
            if ($this->user->authorise('core.edit', $contexts[$pk])) {
                $this->table->reset();
                $this->table->load($pk);

                $feedOptions = new Registry($this->table->params_feed);
                foreach ($newValues as $plugin => $values) {
                    foreach ($values as $name => $value) {
                        if ($value !== null && $value !== '') {
                            $feedOptions->set($plugin . '.' . $name, $value);
                        }
                    }
                }
                $this->table->params_feed = $feedOptions->toString();

                $event = new BeforeBatchEvent(
                    $this->event_before_batch,
                    ['src' => $this->table, 'type' => 'pcf']
                );
                $this->dispatchEvent($event);

                // Check the row.
                if (!$this->table->check()) {
                    $this->setError($this->table->getError());

                    return false;
                }

                if (!$this->table->store()) {
                    $this->setError($this->table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }


    protected function batchCustomFields($newValues, $pks, $contexts): bool
    {
        $this->initBatch();

        foreach ($pks as $pk) {
            if ($this->user->authorise('core.edit', $contexts[$pk])) {
                $this->table->reset();
                $this->table->load($pk);

                $event = new BeforeBatchEvent(
                    $this->event_before_batch,
                    ['src' => $this->table, 'type' => 'com_fields']
                );
                $this->dispatchEvent($event);

                // Check the row.
                if (!$this->table->check()) {
                    $this->setError($this->table->getError());

                    return false;
                }

                foreach ($newValues as $name => $value) {
                    if ($value !== null && $value !== '') {
                        PhocacartFields::saveFieldValue('com_phocacart.phocacartitem', (int)$pk, $name, $value);
                    }
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }
}

