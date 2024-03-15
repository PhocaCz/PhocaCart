<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Phoca\PhocaCart\ContentType\ContentTypeHelper;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartAttribute
{
    public static function getAttributesById($productId, $return = 0, bool $showUnpublished = false)
    {
        $db = Factory::getDBO();


        $query = 'SELECT a.id, '. I18nHelper::sqlCoalesce(['title', 'alias']) . ', a.required, a.type, a.published, a.is_filter, a.attribute_template'
            . ' FROM #__phocacart_attributes AS a'
            . I18nHelper::sqlJoin('#__phocacart_attributes_i18n')
            . ' WHERE a.product_id = ' . (int) $productId;


        if (!$showUnpublished) {
            $query .= ' AND a.published = 1';
        }

        $query .= ' ORDER BY a.ordering';
        $db->setQuery($query);


        if ($return == 0) {
            return $db->loadObjectList();
        } else if ($return == 1) {
            return $db->loadAssocList();
        } else {
            $attributes        = $db->loadAssocList();
            $attributesSubform = [];
            $i                 = 0;
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    $attributesSubform['attributes' . $i]['id']       = (int)$attribute['id'];
                    $attributesSubform['attributes' . $i]['attribute_template'] = (int)$attribute['attribute_template'];
                    $attributesSubform['attributes' . $i]['title']    = (string)$attribute['title'];
                    $attributesSubform['attributes' . $i]['alias']    = (string)$attribute['alias'];
                    $attributesSubform['attributes' . $i]['published'] = (int)$attribute['published'];
                    $attributesSubform['attributes' . $i]['required'] = (int)$attribute['required'];
                    $attributesSubform['attributes' . $i]['is_filter'] = (int)$attribute['is_filter'];
                    $attributesSubform['attributes' . $i]['type']     = (int)$attribute['type'];
                    $i++;
                }
            }
            return $attributesSubform;
        }
    }

    public static function getOptionsById($attributeId, $return = 0, bool $showUnpublished = false) {

        $db = Factory::getDBO();

        $query = 'SELECT a.id, '.I18nHelper::sqlCoalesce(['title', 'alias']).', a.published, a.amount, a.operator, a.stock, a.operator_weight, a.weight, '
            . 'a.image, a.image_medium, a.image_small, a.download_folder, a.download_file, a.download_token, a.color, a.default_value, a.required, a.type, a.sku, a.ean'
            . ' FROM #__phocacart_attribute_values AS a'
            . I18nHelper::sqlJoin('#__phocacart_attribute_values_i18n')
            . ' WHERE a.attribute_id = ' . (int) $attributeId;

        if (!$showUnpublished) {
            $query .= ' AND a.published = 1';
        }
        $query .= ' ORDER BY a.ordering';
        $db->setQuery($query);

        if ($return == 0) {
            return $db->loadObjectList();
        } else {
            return $db->loadAssocList();
        }
    }

    public static function getTypeArray($returnId = 0, $returnValue = 0, $returnFull = 0) {

        // 0 ... Title
        // 1 ... Default Value Type (1 ... multiple default values (checkbox), 2 ... single default value (select box))

        // EDIT PHOCACARTATTRIBUTE (attribute class, attribute layouts)
        $o = array(
            '1' => array(Text::_('COM_PHOCACART_ATTR_TYPE_SELECT'), 0),
            '2' => array(Text::_('COM_PHOCACART_ATTR_TYPE_COLOR_SELECT'), 0),
            '3' => array(Text::_('COM_PHOCACART_ATTR_TYPE_IMAGE_SELECT'), 0),
            '4' => array(Text::_('COM_PHOCACART_ATTR_TYPE_CHECKBOX'), 1),
            '5' => array(Text::_('COM_PHOCACART_ATTR_TYPE_COLOR_CHECKBOX'), 1),
            '6' => array(Text::_('COM_PHOCACART_ATTR_TYPE_IMAGE_CHECKBOX'), 1),
            '7' => array(Text::_('COM_PHOCACART_ATTR_TYPE_TEXT_64'), ''),
            '8' => array(Text::_('COM_PHOCACART_ATTR_TYPE_TEXT_128'), ''),
            '9' => array(Text::_('COM_PHOCACART_ATTR_TYPE_TEXT_256'), ''),
            '10' => array(Text::_('COM_PHOCACART_ATTR_TYPE_TEXTAREA_1024'), ''),
            '11' => array(Text::_('COM_PHOCACART_ATTR_TYPE_TEXTAREA_2048'), ''),
            '12' => array(Text::_('COM_PHOCACART_ATTR_TYPE_TEXT_COLOR_PICKER'), ''),
            '20' => array(Text::_('COM_PHOCACART_ATTR_TYPE_GIFT'), '')


        );

        if ((int)$returnId > 0 && (int)$returnValue > 0) {
            return $o[(int)$returnId][(int)$returnValue];//returnValue: 0 ... Title, 1 ... Default Value Type
        }

        if ((int)$returnId > 0) {
            return $o[(int)$returnId];// whole row
        }
        if ($returnFull == 0) {
            $o2 = array();
            foreach ($o as $k => $v) {
                $o2[$k] = $v[0];
            }
            return $o2; // text and value for select box
        }
        return $o;
    }

    public static function getAttributeLength($type, $typeOption = 0) {

        // EDIT PHOCACARTATTRIBUTE ATTRIBUTETYPE

        switch ($type) {
            case 7:
                return 64;
            break;

            case 8:
                return 128;
            break;

            case 9:
                return 256;
            break;

            case 10:
                return 1024;
            break;

            case 11:
                return 2048;
            break;

            case 12:
                return 7;
            break;

            // GIFT
            case 20:

                if ($typeOption == 20) { return 100;} // recipient name
                if ($typeOption == 21) { return 50;} // recipient email
                if ($typeOption == 22) { return 100;} // sender name
                if ($typeOption == 23) {

                    $paramsC 					= PhocacartUtils::getComponentParameters();
		            $gift_sender_message_length	= $paramsC->get( 'gift_sender_message_length', 500 );

                    return (int)$gift_sender_message_length;
                } // sender message
                if ($typeOption == 24) { return 3;} // gift type
                return 0;
            break;


            default:
                return 0;
            break;
        }
        return 0;
    }

    /*
     * type of attribute
     * value of attribute
     * encoded - is urlencoded yet
     * display - are we asking it for display (we only want to display text attributes value not checkbox or selectboxes which include
     *           numbers (their values are displayed other way
     * type of option
     */

    public static function setAttributeValue($type, $value, $encoded = false, $display = false, $typeOption = 0)
    {
        switch ($type) {
            case 7:
            case 8:
            case 9:
            case 10:
            case 11:
			case 12:
            case 20:

                if ($encoded || $display) {
                    $value = urldecode($value);
                }

                $value = strip_tags($value);
                return urlencode(substr($value, 0, self::getAttributeLength($type, $typeOption)));

            default:
                if ($display) {
                    return '';
                } else {
                    return (int)$value;
                }
        }
    }

    public static function getRequiredArray()
    {
        $o = array('0' => Text::_('COM_PHOCACART_NO'), '1' => Text::_('COM_PHOCACART_YES'));
        return $o;
    }

    public static function getOperatorArray() {
        $o = array('+' => '+', '-' => '-');
        return $o;
    }

    /**
     * @param $productId
     * @param $attributesArray
     * @param int $new
     * @param int $copy used by BATCH and COPY ATTRIBUTES - if copy == 1 then only create new tokens, if copy == 2 then create new tokens and create folder and copy the files from source
     * @throws Exception
     */

    public static function storeAttributesById($productId, $attributesArray, $new = 0, $copy = 0) {
        if ((int)$productId > 0) {
            $db             = Factory::getDBO();
            $app            = Factory::getApplication();
            $pathAttributes = PhocacartPath::getPath('attributefile');// to check if attribute option download file exists

            // When you add or update attributes and options, you need to have some info about which attributes and options
            // are now active - so all others will be removed
            $notDeleteAttribs = array();                              // Select all attributes which will be not deleted
            // Options are defined in attributes array
            $i = 1;
            // ADD ATTRIBUTES
            if (!empty($attributesArray)) {

                foreach ($attributesArray as &$attribute) {
                    if ($attribute['attribute_template']) {
                        $template = ContentTypeHelper::getContentTypeParams(ContentTypeHelper::Attribute, (int)$attribute['attribute_template']);
                        $attribute = array_merge($attribute, $template->toArray());
                    }

                    $i18nData = I18nHelper::prepareI18nData($attribute, ['title', 'alias']);
                    $attribute = PhocacartUtils::arrayDefValues($attribute, [
                       'attribute_template' => null,
                       'published' => 1,
                       'required' => '',
                       'is_filter' => 1,
                       'type' => '',
                    ], [
                        'title' => Factory::getDate()->format("Y-m-d-H-i-s"),
                    ]);

                    if (empty($attribute['alias'])) {
                        $attribute['alias'] = $attribute['title'];
                    }
                    $attribute['alias'] = PhocacartUtils::getAliasName($attribute['alias']);

                    $idExists = 0;
                    if ($new == 0) {
                        if (isset($attribute['id']) && $attribute['id'] > 0) {
                            // Does the row exist
                            $query = ' SELECT id FROM #__phocacart_attributes WHERE id = ' . (int)$attribute['id'] . ' ORDER BY id';
                            $db->setQuery($query);
                            $idExists = $db->loadResult();
                        }
                    }

                    if ((int)$idExists > 0) {
                        $query = 'UPDATE #__phocacart_attributes SET'
                            . ' product_id = ' . (int)$productId . ','
                            . ' attribute_template = ' . ((int)$attribute['attribute_template'] ?: 'null')  . ','
                            . ' title = ' . $db->quote($attribute['title']) . ','
                            . ' alias = ' . $db->quote($attribute['alias']) . ','
                            . ' published = ' . (int)$attribute['published'] . ','
                            . ' required = ' . (int)$attribute['required'] . ','
                            . ' is_filter = ' . (int)$attribute['is_filter'] . ','
                            . ' type = ' . (int)$attribute['type'] . ','
                            . ' ordering = ' . $i
                            . ' WHERE id = ' . (int)$idExists;
                        $db->setQuery($query);
                        $db->execute();
                        $i++;
                        $newIdA = $idExists;
                    } else {
                        $date = Factory::getDate();
                        $now  = $date->toSql();

                        $valuesString = '(' . (int)$productId . ', '
                            . ((int)$attribute['attribute_template'] ?: 'null') . ', '
                            . $db->quote($attribute['title']) . ', '
                            . $db->quote($attribute['alias']) . ', '
                            . (int)$attribute['published'] . ', '
                            . (int)$attribute['required'] . ', '
                            . (int)$attribute['is_filter'] . ', '
                            . (int)$attribute['type'] . ', '
                            . $db->quote($now) . ', '
                            .  $i . ')';
                        $query = ' INSERT INTO #__phocacart_attributes (product_id, attribute_template, title, alias, published, required, is_filter, type, date, ordering)'
                            . ' VALUES ' . $valuesString;
                        $db->setQuery($query);
                        $db->execute(); // insert is not done together but step by step because of getting last insert id

                        $i++;
                        // ADD OPTIONS
                        $newIdA = $db->insertid();
                    }

                    I18nHelper::saveI18nData($newIdA, $i18nData, '#__phocacart_attributes_i18n');
                    $notDeleteAttribs[] = $newIdA;

                    $notDeleteOptions = [];// Select all options which will be not deleted

                    if (!empty($attribute['options']) && isset($newIdA) && (int)$newIdA > 0) {
                        // Get Default Value Type - if the attribute type is single select box or multiple checkbox
                        // If 1 ... it is multiple, you don't need to check for unique default value
                        // If 0 ... it is single, you need to check that the attribute has selected only one value
                        $dTV = self::getTypeArray($attribute['type'], 1);
                        $dI  = 0;// defaultValue $i
                        $dVR = 0;// defaultValue removed?
                        $j   = 0;// ordering

                        foreach ($attribute['options'] as &$option) {
                            $i18nData = I18nHelper::prepareI18nData($option, ['title', 'alias']);

                            $option = PhocacartUtils::arrayDefValues($option, [
                                'alias' => '',
                                'amount' => '',
                                'operator' => '1',
                                'stock' => '',
                                'operator_weight' => '',
                                'weight' => '0.0',
                                'image' => '',
                                'image_medium' => '',
                                'image_small' => '',
                                'download_folder' => '',
                                'download_file' => '',
                                'download_token' => '',
                                'color' => '',
                                'required' => '0',
                                'type' => '0',
                                'ean' => '',
                                'sku' => '',
                            ], [
                                'title' => Factory::getDate()->format("Y-m-d-H-i-s"),
                            ]);

                            if (!isset($option['published'])) {
                                $option['published'] = '1';
                            }

                            if (empty($option['alias'])) {
                                $option['alias'] = $option['title'];
                            }
                            $option['alias'] = PhocacartUtils::getAliasName($option['alias']);

                            // Transform checkbox to INT (1 or 0)
                            // And check if there are more default values which is not possible e.g. for select box
                            $defaultValue = 0;
                            //PhocacartLog::add(3, $v['title'] . '- '. $v2['title']. $v2['type']. ' - '. $v2['default_value']);

                            // can be "on" (sent by form) or "0" or "1" sent by database e.g. in batch
                            if (isset($option['default_value']) && $option['default_value'] != '0') {
                                $defaultValue = 1;

                                //  SELECTBOX OR TEXT
                                if ($dTV == 0 || $dTV == '') {
                                    $dI++;
                                }

                                // Example: we are in loop of options of select box
                                // User has selected two default values (checked)
                                // But select box can have only one default value, so we need to skip it and inform user
                                if ($dTV == 0 && (int)$dI > 1) {
                                    // SELECT - only one default value
                                    $defaultValue = 0;
                                    $dVR          = 1;
                                } else if ($dTV === '' && (int)$dI > 0) {
                                    // TEXT - no default value
                                    $defaultValue = 0;
                                    $dVR          = 1;
                                }
                            }

                            $option['amount'] = PhocacartText::filterValue($option['amount'], 'number3');

                            // COPY OR BATCH functions - we cannot do the same tokens so create new token and token folder and if set copy the files
                            // EACH ATTRIBUTE OPTION DOWNLOAD FILE MUST HAVE UNIQUE DOWNLOAD TOKEN AND DOWNLOAD FOLDER
                            if ($copy > 0) {
                                // First create new token and token folder
                                $oldDownloadFolder     = $option['download_folder'];
                                $option['download_token']  = PhocacartUtils::getToken();
                                $option['download_folder'] = PhocacartUtils::getAndCheckToken('folder', PhocacartPath::getPath('attributefile'));
                                //$option['download_folder']			= PhocacartUtils::getToken('folder');

                                if ($copy == 2 && $option['download_file'] != '' && File::exists($pathAttributes['orig_abs_ds'] . $option['download_file'])) {
                                    $newDownloadFile = str_replace($oldDownloadFolder, $option['download_folder'], $option['download_file']);
                                    if (!Folder::create($pathAttributes['orig_abs_ds'] . $option['download_folder'])) {
                                        // Error message will be set below: COM_PHOCACART_ERROR_DOWNLOAD_FILE_OF_ATTRIBUTE_OPTION_DOES_NOT_EXIST
                                    }

                                    if (!File::copy($pathAttributes['orig_abs_ds'] . $option['download_file'], $pathAttributes['orig_abs_ds'] . $newDownloadFile)) {
                                        // Error message will be set below: COM_PHOCACART_ERROR_DOWNLOAD_FILE_OF_ATTRIBUTE_OPTION_DOES_NOT_EXIST
                                    }
                                    $option['download_file'] = $newDownloadFile;
                                } else {
                                    $option['download_file'] = '';
                                }
                            }

                            // CHECK DOWNLOAD FILE
                            if ($option['download_file'] != '' && $option['download_folder'] == '') {
                                $msg = Text::_('COM_PHOCACART_ATTRIBUTE') . ': ' . $attribute['title'] . "<br />";
                                $msg .= Text::_('COM_PHOCACART_ERROR_DOWNLOAD_FILE_DOES_NOT_INCLUDE_DOWNLOAD_FOLDER');
                                $app->enqueueMessage($msg, 'error');
                            }

                            // If download_file does not exist on the server, remove it
                            if ($option['download_file'] != '' && !File::exists($pathAttributes['orig_abs_ds'] . $option['download_file'])) {
                                $option['download_file'] = '';
                                $msg                 = Text::_('COM_PHOCACART_ATTRIBUTE') . ': ' . $attribute['title'] . "<br />";
                                $msg                 .= Text::_('COM_PHOCACART_ERROR_DOWNLOAD_FILE_OF_ATTRIBUTE_OPTION_DOES_NOT_EXIST');
                                $app->enqueueMessage($msg, 'error');
                            }

                            $idExists = 0;

                            if ($new == 0) {
                                if (isset($option['id']) && $option['id'] > 0) {
                                    // Does the row exist
                                    $query = ' SELECT id '
                                        . ' FROM #__phocacart_attribute_values'
                                        . ' WHERE id = ' . (int)$option['id']
                                        . ' ORDER BY id';
                                    $db->setQuery($query);
                                    $idExists = $db->loadResult();
                                }
                            }

                            if ((int)$idExists > 0) {
                                $option['amount'] = PhocacartUtils::replaceCommaWithPoint($option['amount']);
                                $option['weight'] = PhocacartUtils::replaceCommaWithPoint($option['weight']);


                                $query = 'UPDATE #__phocacart_attribute_values SET'
                                    . ' attribute_id = ' . (int)$newIdA . ','
                                    . ' title = ' . $db->quote($option['title']) . ','
                                    . ' alias = ' . $db->quote($option['alias']) . ','
                                    . ' published = ' . (int)$option['published'] . ','
                                    . ' operator = ' . $db->quote($option['operator']) . ','
                                    . ' amount = ' . $db->quote($option['amount']) . ','
                                    . ' stock = ' . (int)$option['stock'] . ','
                                    . ' operator_weight = ' . $db->quote($option['operator_weight']) . ','
                                    . ' weight = ' . $db->quote($option['weight']) . ','
                                    . ' image = ' . $db->quote($option['image']) . ','
                                    . ' image_medium = ' . $db->quote($option['image_medium']) . ','
                                    . ' image_small = ' . $db->quote($option['image_small']) . ','
                                    . ' download_folder = ' . $db->quote($option['download_folder']) . ','
                                    . ' download_file = ' . $db->quote($option['download_file']) . ','
                                    . ' download_token = ' . $db->quote($option['download_token']) . ','
                                    . ' color = ' . $db->quote($option['color']) . ','
                                    . ' default_value = ' . (int)$defaultValue . ','
                                    . ' required = ' . (int)$option['required'] . ','
                                    . ' type = ' . (int)$option['type'] . ','
                                    . ' ean = ' . $db->quote($option['ean']) . ','
                                    . ' sku = ' . $db->quote($option['sku']) . ','
                                    . ' ordering = ' . (int)$j
                                    . ' WHERE id = ' . (int)$idExists;


                                $db->setQuery($query);
                                $db->execute();
                                $j++;

                                $newIdO = $idExists;
                            } else {
                                $option['amount'] = PhocacartUtils::replaceCommaWithPoint($option['amount']);
                                $option['weight'] = PhocacartUtils::replaceCommaWithPoint($option['weight']);

                                $options = '(' . (int)$newIdA . ', '
                                    . $db->quote($option['title']) . ', '
                                    . $db->quote($option['alias']) . ', '
                                    . (int)$option['published'] . ', '
                                    . $db->quote($option['operator']) . ', '
                                    . $db->quote($option['amount']) . ', '
                                    . (int)$option['stock'] . ', '
                                    . $db->quote($option['operator_weight']) . ', '
                                    . $db->quote($option['weight']) . ', '
                                    . $db->quote($option['image']) . ', '
                                    . $db->quote($option['image_medium']) . ', '
                                    . $db->quote($option['image_small']) . ', '
                                    . $db->quote($option['download_folder']) . ','
                                    . $db->quote($option['download_file']) . ','
                                    . $db->quote($option['download_token']) . ','
                                    . $db->quote($option['color']) . ', '
                                    . (int)$defaultValue . ','
                                    . (int)$option['required'] . ', '
                                    . (int)$option['type'] . ', '
                                    . $db->quote($option['ean']) . ', '
                                    . $db->quote($option['sku']) . ', '
                                    . (int)$j . ')';


                                $query = ' INSERT INTO #__phocacart_attribute_values (attribute_id, title, alias, published, operator, amount, stock, operator_weight, weight, image, image_medium, image_small, download_folder, download_file, download_token, color, default_value, required, type, ean, sku, ordering)'
                                    . ' VALUES ' . $options;

                                $db->setQuery($query);
                                $db->execute();
                                $j++;
                                $newIdO = $db->insertid();
                            }

                            I18nHelper::saveI18nData($newIdO, $i18nData, '#__phocacart_attribute_values_i18n');
                            $notDeleteOptions[] = $newIdO;
                        }


                        // One or more default values removed
                        if ($dVR == 1) {
                            $msg = Text::_('COM_PHOCACART_ATTRIBUTE') . ': ' . $attribute['title'] . "<br />";
                            $msg .= Text::_('COM_PHOCACART_THIS_ATTRIBUTE_DOES_NOT_ALLOW_TO_STORE_DEFAULT_VALUES_OR_MULTIPLE_DEFAULT_VALUES');
                            $app->enqueueMessage($msg, 'error');
                        }
                    }


                    // Remove all options except the active
                    $qS = ' SELECT download_folder, download_file FROM #__phocacart_attribute_values WHERE attribute_id = ' . (int)$newIdA;
                    if (I18nHelper::isI18n()) {
                        $query = 'DELETE v, i18n FROM #__phocacart_attribute_values v'
                            . ' LEFT JOIN #__phocacart_attribute_values_i18n i18n ON i18n.id = v.id'
                            . ' WHERE v.attribute_id = ' . (int) $newIdA;
                    } else {
                        $query = 'DELETE v FROM #__phocacart_attribute_values v WHERE v.attribute_id = ' . (int)$newIdA;
                    }
                    if (!empty($notDeleteOptions)) {
                        $qS .= ' AND id NOT IN (' . implode(',', $notDeleteOptions) . ')';
                        $query .= ' AND v.id NOT IN (' . implode(',', $notDeleteOptions) . ')';
                    }

                    $db->setQuery($qS);
                    $folderFiles = $db->loadAssocList();
                    self::removeDownloadFolderAndFiles($folderFiles, $pathAttributes);

                    $db->setQuery($query);
                    $db->execute();
                }
            }

            // Remove all attributes except the active
            $qS = ' SELECT v.download_folder, v.download_file FROM #__phocacart_attribute_values AS v'
                . ' LEFT JOIN #__phocacart_attributes AS a ON a.id = v.attribute_id'
                . ' WHERE a.product_id = ' . (int)$productId;

            if (I18nHelper::isI18n()) {
                $query = ' DELETE a, v, i18n, i18n_v FROM #__phocacart_attributes a'
                    . ' LEFT JOIN #__phocacart_attributes_i18n i18n ON i18n.id = a.id'
                    . ' LEFT JOIN #__phocacart_attribute_values v ON v.attribute_id = a.id'
                    . ' LEFT JOIN #__phocacart_attribute_values_i18n i18n_v ON i18n_v.id = v.id'
                    . ' WHERE a.product_id = ' . (int)$productId;
            } else {
                $query = ' DELETE a, v FROM #__phocacart_attributes a'
                    . ' LEFT JOIN #__phocacart_attribute_values v ON v.attribute_id = a.id'
                    . ' WHERE a.product_id = ' . (int)$productId;
            }

            if (!empty($notDeleteAttribs)) {
                $qS .= ' AND a.id NOT IN (' . implode(',', $notDeleteAttribs) . ')';
                $query .= ' AND a.id NOT IN (' . implode(',', $notDeleteAttribs) . ')';
            }

            $db->setQuery($qS);
            $folderFiles = $db->loadAssocList();
            self::removeDownloadFolderAndFiles($folderFiles, $pathAttributes);

            $db->setQuery($query);
            $db->execute();

        }

    }

    public static function removeDownloadFolderAndFiles($folderFiles, $pathAttributes) {
        if (!empty($folderFiles)) {
            foreach ($folderFiles as $kF => $vF) {
                // Folder will remove the file(s) too
                if (Folder::exists($pathAttributes['orig_abs_ds'] . $vF['download_folder'])) {
                    Folder::delete($pathAttributes['orig_abs_ds'] . $vF['download_folder']);
                }
            }
        }
    }

    public static function getAttributesAndOptions($productId) {
        $attributes = self::getAttributesById($productId);

        $attributesKey = [];
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attributesKey[$attribute->id] = $attribute;
                $attributesKey[$attribute->id]->options = false;

                $options = self::getOptionsById($attribute->id);
                if ($options) {
                    $optionsKey = [];
                    foreach ($options as $option) {
                        $optionsKey[$option->id] = $option;
                    }
                    $attributesKey[$attribute->id]->options = $optionsKey;
                }
            }
        }

        return $attributesKey;
    }

    public static function getAllAttributesAndOptions($ordering = 1, $onlyAvailableProducts = 0, $lang = '', $filterProducts = [], $limitToIsFilter = false) {

        $db           = Factory::getDBO();
        $orderingText = PhocacartOrdering::getOrderingText($ordering, 5);


        $columns    = 'v.id, v.image, v.image_medium, v.image_small, v.download_folder, v.download_file, v.download_token, v.color, v.default_value, v.required, v.type, at.id AS attrid, at.title AS attrtitle, at.alias AS attralias, at.type as attrtype';
        $groupsFull = 'v.id, v.image, v.image_medium, v.image_small, v.download_folder, v.download_file, v.download_token, v.color, v.default_value, v.required, v.type attralias, at.id, at.type';

        $groupsFull .= $columns . ', v.title, v.alias, at.title, at.alias';

        $columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'v', '', '', ',');
        $columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'at', 'attr', '', ',');

        /*if (I18nHelper::useI18n()) {
            $groupsFull = $columns . ', coalesce(i18n_v.title, v.title), coalesce(i18n_v.alias, v.alias)';
            $groupsFull = $columns . ', coalesce(i18n_at.title, at.title), coalesce(i18n_at.alias, at.alias)';
            $columns .= ', coalesce(i18n_v.title, v.title) as title, coalesce(i18n_v.alias, v.alias) as alias';
            $columns .= ', coalesce(i18n_at.title, at.title) as attrtitle, coalesce(i18n_at.alias, at.alias) as attralias';
        } else {
            $columns   .= ', v.title, v.alias, at.title AS attrtitle, at.alias AS attralias';
            $groupsFull .= ', v.title, v.alias, at.title, at.alias';
        }*/



        $groupsFast = 'v.id';
        $groups     = PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

        $wheres = array();
        $lefts  = array();

        $lefts[] = ' #__phocacart_attributes AS at ON at.id = v.attribute_id';

        $productTableAdded = 0;

        if ($onlyAvailableProducts == 1) {

            if ($lang != '' && $lang != '*') {
                $wheres[] = PhocacartUtilsSettings::getLangQuery('p.language', $lang);
            }

            $lefts[] = ' #__phocacart_products AS p ON at.product_id = p.id';
            $productTableAdded = 1;
            $rules   = PhocacartProduct::getOnlyAvailableProductRules();
            $wheres  = array_merge($wheres, $rules['wheres']);
            $lefts   = array_merge($lefts, $rules['lefts']);
        } else {

            if ($lang != '' && $lang != '*') {
                $wheres[] = PhocacartUtilsSettings::getLangQuery('p.language', $lang);
                $lefts[]  = ' #__phocacart_products AS p ON at.product_id = p.id';
                $productTableAdded = 1;
            }
        }

        if ($limitToIsFilter) {
            $wheres[] = 'at.is_filter = 1';
        }

        if (!empty($filterProducts)) {
            $productIds = implode(',', $filterProducts);
            $wheres[]   = 'p.id IN (' . $productIds . ')';
            if ($productTableAdded == 0) {
                $lefts[]  = ' #__phocacart_products AS p ON at.product_id = p.id';
            }
        }

        $q = ' SELECT ' . $columns
            . ' FROM  #__phocacart_attribute_values AS v'
            . (!empty($lefts) ? ' LEFT JOIN ' . implode(' LEFT JOIN ', $lefts) : '')
            . I18nHelper::sqlJoin('#__phocacart_attributes_i18n', 'at')
            . I18nHelper::sqlJoin('#__phocacart_attribute_values_i18n', 'v')
            . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
            . ' GROUP BY ' . $groups
            . ' ORDER BY ' . $orderingText;


        $db->setQuery($q);
        $attributes = $db->loadObjectList();

        $a = array();
        if (!empty($attributes)) {
            foreach ($attributes as $k => $v) {
                if (isset($v->attrtitle) && $v->attrtitle != ''
                    && isset($v->attrid) && $v->attrid != ''
                    && isset($v->attralias) && $v->attralias != '') {
                    $a[$v->attralias]['title'] = $v->attrtitle;
                    $a[$v->attralias]['id']    = $v->attrid;
                    $a[$v->attralias]['alias'] = $v->attralias;
                    $a[$v->attralias]['type']  = $v->attrtype;
                    if (isset($v->title) && $v->title != ''
                        && isset($v->id) && $v->id != ''
                        && isset($v->alias) && $v->alias != '') {
                        $a[$v->attralias]['options'][$v->alias]                  = new stdClass();
                        $a[$v->attralias]['options'][$v->alias]->title           = $v->title;
                        $a[$v->attralias]['options'][$v->alias]->id              = $v->id;
                        $a[$v->attralias]['options'][$v->alias]->alias           = $v->alias;
                        $a[$v->attralias]['options'][$v->alias]->image           = $v->image;
                        $a[$v->attralias]['options'][$v->alias]->image_small     = $v->image_small;
                        $a[$v->attralias]['options'][$v->alias]->download_folder = $v->download_folder;
                        $a[$v->attralias]['options'][$v->alias]->download_file   = $v->download_file;
                        $a[$v->attralias]['options'][$v->alias]->download_token  = $v->download_token;
                        $a[$v->attralias]['options'][$v->alias]->color           = $v->color;
                        $a[$v->attralias]['options'][$v->alias]->default_value   = $v->default_value;
                        $a[$v->attralias]['options'][$v->alias]->required        = $v->required;
                        $a[$v->attralias]['options'][$v->alias]->type            = $v->type;
                    } else {
                        $a[$v->attralias]['options'] = array();
                    }
                }
            }

        }
        return $a;

    }

    public static function getAttributeValue($id, $attributeId) {
        $db    = Factory::getDBO();
        /*if (I18nHelper::useI18n()) {
            $columns = 'coalesce(i18n_a.title, a.title) as title, coalesce(i18n_a.alias, a.alias) as alias, coalesce(i18n_aa.title, aa.title) as atitle';
        } else {
            $columns = 'a.title, a.alias, aa.title as atitle';
        }*/

        $columns = I18nHelper::sqlCoalesce(['title', 'alias']);
        $columns .= I18nHelper::sqlCoalesce(['title'], 'aa', 'a', '', ',');

        $query = ' SELECT a.id, a.type, a.amount, a.operator, a.weight, a.operator_weight, a.operator_volume, '
            . 'a.stock, a.image, a.image_medium, a.image_small, a.download_folder, a.download_file, a.download_token, a.color, a.default_value, a.required, a.type,'
            . ' aa.id as aid, aa.type as atype, ' . $columns
            . ' FROM #__phocacart_attribute_values AS a'
            . ' LEFT JOIN #__phocacart_attributes AS aa ON a.attribute_id = aa.id'
            . I18nHelper::sqlJoin('#__phocacart_attribute_values_i18n')
            . I18nHelper::sqlJoin('#__phocacart_attributes_i18n', 'aa')
            . ' WHERE a.id = ' . (int)$id . ' AND a.attribute_id = ' . (int)$attributeId
            . ' ORDER BY a.id'
            . ' LIMIT 1';
        $db->setQuery($query);
        $attrib = $db->loadObject();
        return $attrib;

    }

    public static function getAttributeFullValues($attributes) {

        $fullAttributes = array();
        if (!empty($attributes)) {
            foreach ($attributes as $k => $v) {
                $fullAttributes[$k] = new stdClass();
                // Could be set a function to get info about the attribute, for now not needed
                if (!empty($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ((int)$k > 0 && (int)$k2 > 0) {
                            $attrib                           = PhocacartAttribute::getAttributeValue((int)$k2, (int)$k);
                            $fullAttributes[$k]->options[$k2] = $attrib;
                        }
                    }
                }
            }
        }
        return $fullAttributes;
    }

    public static function getAllRequiredAttributesByProduct($productId) {

        $wheres   = array();
        $wheres[] = ' a.id = ' . (int)$productId;
        $db       = Factory::getDBO();

        // 1) Select required attributes
        $query    = ' SELECT at.id, at.type, "1" AS required_type, "" AS options'
            . ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1'
            . ' WHERE ' . implode(' AND ', $wheres)
            . ' ORDER BY a.id';
        $db->setQuery($query);
        $attributes = $db->loadAssocList('id');


        // Select required options of specific attributes of attributes which are not required (so $attributes and $attributesOptions will no cover each other)
        $query    = ' SELECT av.id as option_id, at.id, at.type, "2" AS required_type, "" as options'
            . ' FROM #__phocacart_products AS a'
            . ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 0'
            . ' LEFT JOIN #__phocacart_attribute_values AS av ON at.id = av.attribute_id AND av.id > 0 AND av.required = 1'
            . ' WHERE ' . implode(' AND ', $wheres)
            . ' ORDER BY a.id';
        $db->setQuery($query);
        $attributesOptions = $db->loadAssocList();


        // correct empty attributes and add attributes which have required options but are not required themselves
        if (!empty($attributes)) {

            if (!empty($attributesOptions)) {

                foreach($attributesOptions as $k => $v) {

                    if (isset($v['id']) && $v['id'] > 0 && isset($v['option_id']) && $v['option_id'] > 0) {
                        $idA = $v['id'];
                        $idO = $v['option_id'];
                        $attributes[$idA]['id']              = $idA;
                        $attributes[$idA]['options'][$idO]   = $v['option_id'];
                        $attributes[$idA]['required_type']   = $v['required_type'];
                        $attributes[$idA]['type']            = $v['type'];
                    }

                }

            }


            foreach ($attributes as $k => $v) {
                if (!$v['id'] && !$v['type']) {
                    unset($attributes[$k]);
                }
            }
        }


        return $attributes;
    }

    /*
    public static function getType($id) {

        switch((int)$id) {
            case 4:
                $type = 2;//Multiple Value - handle array
            break;

            case 1:
            case 2:
            case 3:
            default:
                $type = 1;//Single Value - handle string
            break;
        }
        return $type;
    }*/

    public static function checkRequiredAttributes($id, $attributes) {

        // PHOCARTATTRIBUTE ATTRIBUTETYPE

        // Covert all attribute values from strings to array
        if (!empty($attributes)) {
            foreach ($attributes as $k => $v) {
                if (!is_array($v)) {
                    $attributes[$k] = array(0 => $v);
                }
            }
        }

        // $attributes - attributes sent per form when adding product to cart
        // $requiredAttributes - all required attributes for selected product
        // Get all required attributes for this product
        // Or required options of not required attributes (specific case for text, textarea, gift, etc. attributes where only one option can be required, not whole attribute)
        $requiredAttributes = PhocacartAttribute::getAllRequiredAttributesByProduct($id);


        $msgA          = array();
        $passAll       = true;
        $passAttribute = array();

        if (!empty($requiredAttributes)) {
            foreach ($requiredAttributes as $k2 => $v2) {

                if (isset($v2['id']) && $v2['id'] > 0) {


                    if (!empty($attributes)) {

                        foreach ($attributes as $k3 => $v3) {


                            if (isset($k3) && (int)$k3 == (int)$v2['id']) {
                                $passAttribute[$k3] = 0;

                                if (!empty($v3)) {

                                    foreach ($v3 as $k4 => $v4) {


                                        if (isset($v2['type']) && ($v2['type'] == 7 || $v2['type'] == 8 || $v2['type'] == 9 || $v2['type'] == 10 || $v2['type'] == 11 || $v2['type'] == 12 || $v2['type'] == 20)) {
                                            // -------------------------------------
                                            // ATTRIBUTE TYPE = TEXT, TEXTAREA, GIFT
                                            // -------------------------------------

                                            // 1) FIRST test required options (not required attribute)
                                            // required options in attributes which are not required
                                            // because there can be required whole attribute but only one option
                                            if ($v2['required_type'] == 2 && !empty($v2['options'])) {

                                               // Is current option required - is current option ID included in required option field?
                                               if (in_array($k4, $v2['options'])) {

                                                   if (isset($v4['ovalue']) && urldecode($v4['ovalue'] != '')) {
                                                        // Order product - we found value in order of products - OK
                                                        $passAttribute[$k3] = 1;
                                                        //break 2;
                                                    } else if (!is_array($v4) && urldecode($v4 != '')) {
                                                        // Order product - we found value in order of products - OK
                                                        $passAttribute[$k3] = 1;
                                                        //break 2;
                                                    } else {
                                                        $passAll = false;
                                                        break 2;
                                                    }

                                               } else {
                                                   // It is not in required field, set is as OK (can be overriden in loop by other option for this attribute)
                                                   $passAttribute[$k3] = 1;
                                               }

                                            } else {
                                                // 2) SECOND test required attribute

                                                // There is reverse testing to select or checkbox
                                                // In select or checkbox we can wait for some of the option will be selected
                                                // but by text all input text fields in one attribute must be required
                                                if (isset($v4['ovalue']) && urldecode($v4['ovalue'] != '')) {
                                                    // Order product - we found value in order of products - OK
                                                    $passAttribute[$k3] = 1;
                                                    //break 2;
                                                } else if (!is_array($v4) && urldecode($v4 != '')) {
                                                    // Order product - we found value in order of products - OK
                                                    $passAttribute[$k3] = 1;
                                                    //break 2;
                                                } else {
                                                    $passAll = false;
                                                    break 2;
                                                }

                                            }

                                        } else {
                                            // ---------------------------------
                                            // ATTRIBUTE TYPE = CHECKBOX, SELECT
                                            // ---------------------------------

                                            if (isset($v4['oid']) && $v4['oid'] > 0) {
                                                // Order product - we found value in order of products - OK
                                                $passAttribute[$k3] = 1;
                                                break 2;
                                            } else if (!is_array($v4) && (int)$v4 > 0) {
                                                // Add to cart - we found value when adding product to cart - OK
                                                $passAttribute[$k3] = 1;
                                                break 2;
                                            }

                                        }
                                        // possible break 3;
                                    }
                                }
                            }
                        }
                    } else {
                        $msgA[] = 'No FORM ATTRIBUTE found';
                    }
                } else {
                    $msgA[] = 'No ID found of REQUIRED ATTRIBUTE';
                }

                // Summarization of passed values
                $aId = (int)$v2['id'];// ID of attribute
                if (isset($passAttribute[$aId]) && $passAttribute[$aId] == 1) {
                    // this required attribute is OK
                } else {
                    // we didn't found any information - any passed information about this required attribute

                    $passAll = false;
                }
            }

        }


        if (!empty($msgA)) {
            //$u = PhocacartUser::getUserInfo();
            //PhocacartLog::add(3, implode(' ', $msgA), $id, 'IP: '. $u['ip'].', User ID: '.$u['id'] . ', User Name: '.$u['username']);
        }
        return $passAll;
    }

    public static function isMultipleAttribute($type) {

        switch ($type) {
            case 4:
            case 5:
            case 6:
                return true;
            break;
            default:
                return false;
            break;
        }
    }


    /* When product is displayed, it has selected the default values
     * We need to filter all attributes assigned to product so the product only includes selected attributes and otpions
     * and we can make by this selection productKey
     */

    public static function getAttributesSelectedOnly($attributes) {

        $sAttributes = array();
        if (!empty($attributes)) {
            foreach ($attributes as $k => $v) {
                if (!empty($v->options)) {
                    foreach ($v->options as $k2 => $v2) {
                        if (isset($v2->default_value) && $v2->default_value == 1) {
                            $sAttributes[$k][$v2->id] = $v2->id;
                        }
                    }
                }
            }
        }
        return $sAttributes;
    }

    public static function getAttributesSanitizeOptionArray($attributes) {

        $aA = array();
        if (!empty($attributes)) {
            foreach ($attributes as $k => $v) {

                if (!empty($v)) {
                    foreach ($v as $k2 => $v2) {

                        if(isset($v2['oid']) && (int)$v2['oid'] > 0) {

                            $aInt = (int)$k;
                            $oInt = (int)$k2;
                            $aA[$aInt][$oInt] = (int)$v2['oid'];
                        }
                    }
                }
            }
        }
        return $aA;
    }


    public static function sanitizeAttributeArray($attribute) {

        // Sanitanize data and do the same level for all attributes:
        // select attribute = 1
        // checkbox attribute = array(0 => 1, 1 => 1) attribute[]
        $aA = array();
        if (!empty($attribute)) {
            foreach ($attribute as $k => $v) {
                if (is_array($v) && !empty($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ((int)$v2 > 0) {
                            $aA[(int)$k][(int)$v2] = (int)$v2;
                        }
                    }
                } else {
                    if ((int)$v > 0) {
                        $aA[(int)$k][(int)$v] = $v;
                    }
                }
            }
        }
        return $aA;

    }




    public static function makeCombination($array, $requiredArray) {


        $method         = 0;
        $workingArray   = array();
        $arrayNew       = array();

        if (!empty($array)) {
            foreach ($array as $k => $v) {
                if (isset($v['multiple']) && $v['multiple']) {
                    // One of the attribute is multiple - we need to use method 1 which can be hard on the memory
                    $method = 1;
                    break;
                }

                if (isset($v['required']) && $v['required'] == 0) {
                    // One of the attribute is not required - we need to use method 1 which can be hard on the memory
                    $method = 1;
                    break;
                }

                // Working array can be used only by 2. Method (no multiple, all required)
                $aid = (int)$v['aid'];
                $oid = (int)$v['oid'];
                $workingArray[$aid][$oid] = $v;
            }
        }




        if ($method == 1) {

            /* ==== 1. Method ====
               - This method takes so much memory and time (because it counts all possible combinations of each attribute)
               - a) can be used by select boxes
               - b) can be used by checkboxes
               - c) can be used by not required attibutes
            */
            /*
             * based on: stackoverflow.com/questions/1256117/algorithm-that-will-take-numbers-or-words-and-find-all-possible-combinations
             * by Adi Bradfield
             */


            $bits     = count($array); //bits of binary number equal to number of words in query;
            $dec      = 1;             //Convert decimal number to binary with set number of bits, and split into array


            while ($dec < pow(2, $bits)) {

                $binary = str_split(str_pad(decbin($dec), $bits, '0', STR_PAD_LEFT));

                $current          = array();
                $current['title'] = '';
                $current['valid'] = 1;
                $cannotCobminate  = array();

                $i = 0;

                while ($i < ($bits)) {


                    if ($binary[$i] == 1) {



                        $current['product_id']    = $array[$i]['pid'];
                        $current['product_title'] = $array[$i]['ptitle'];

                        // Attribute, Option ID
                        $aid = $array[$i]['aid'];
                        $oid = $array[$i]['oid'];

                        // Title
                        if (isset($current['title']) && $current['title'] != '') {
                            $current['title'] .= ' <span class="ph-attribute-option-item">' . $array[$i]['atitle'] . ': ' . $array[$i]['otitle'] . '</span>';
                        } else {
                            $current['title'] = '<span class="ph-attribute-option-item">' . $array[$i]['atitle'] . ': ' . $array[$i]['otitle'] . '</span>';
                        }


                        $current['attributes'][$aid][$oid] = (int)$oid;


                        // Options inside one select cannot be combinated togeter
                        if (!$array[$i]['multiple']) {
                            if (isset($cannotCobminate[$aid]) && $cannotCobminate[$aid] > 0) {
                                // there is one option selected from select box,
                                // this attribute cannot be combinated in this form
                                $current['valid'] = 0;
                            } else {
                                $cannotCobminate[$aid] = 1;
                            }
                        }

                    }
                    $i++;

                }


                // Define

                $key                      = PhocacartProduct::getProductKey($current['product_id'], $current['attributes']);
                $current['product_id']    = $current['product_id'];
                $current['product_key']   = $key;
                $current['product_title'] = $current['product_title'];
                $current['stock']         = 0;
                $current['price']         = '';
                $current['ean']           = '';
                $current['sku']           = '';
                $current['image']         = '';



                // DEBUG
               /* echo "Iteration: $dec <table cellpadding=\"5\" border=\"1\"><tr>";
                foreach($binary as $b){
                    echo "<td>$b</td>";
                }
                echo "</tr><tr>";
                foreach($array as $l){
                    echo "<td>".$l['otitle']."</td>";
                }
                echo "</tr></table>Output: ";
                foreach($current as $c){
                   // echo $c." ";
                }
                echo "<br><br>";*/


                if (!empty($requiredArray)) {
                    foreach ($requiredArray as $k => $v) {
                        if (!array_key_exists($v, $current['attributes'])) {
                            $current['valid'] = 0;
                        }
                    }
                }


                // Add only such attribute combinations which are possible (two options from select box is not possible)
                if ($current['valid'] == 1) {
                    $arrayNew[$key] = $current;
                }
                $dec++;
            }
        } else {

            /* ==== 2. Method ====
               - This methods is faster than method 1 but can be used for select boxes only.
               - a) can be used by select boxes
               - b) CANNOT be used by checkboxes
               - c) CANNOT be used by not required attibutes
            */


            // https://gist.github.com/cecilemuller/4688876
            $result = array(array());

            if (!empty($workingArray)) {

                foreach ($workingArray as $property => $property_values) {
                    $tmp = array();
                    foreach ($result as $result_item) {
                        foreach ($property_values as $property_value) {
                            $tmp[] = array_merge($result_item, array($property => $property_value));
                        }
                    }
                    $result = $tmp;
                }

                if (!empty($result)) {
                    foreach ($result as $k => $v) {

                        if (!empty($v)) {

                            $current          = array();
                            $current['title'] = '';
                            $current['valid'] = 1;

                            foreach ($v as $k2 => $v2) {

                                $current['product_id']    = $v2['pid'];
                                $current['product_title'] = $v2['ptitle'];

                                // Attribute, Option ID
                                $aid = (int)$v2['aid'];
                                $oid = (int)$v2['oid'];

                                // Title
                                if (isset($current['title']) && $current['title'] != '') {
                                    $current['title'] .= ' <span class="ph-attribute-option-item">' . $v2['atitle'] . ': ' . $v2['otitle'] . '</span>';
                                } else {
                                    $current['title'] = '<span class="ph-attribute-option-item">' . $v2['atitle'] . ': ' . $v2['otitle'] . '</span>';
                                }

                                $current['attributes'][$aid][$oid] = $oid;

                            }

                            // Define
                            $key                      = PhocacartProduct::getProductKey($current['product_id'], $current['attributes']);
                            $current['product_id']    = $current['product_id'];
                            $current['product_key']   = $key;
                            $current['product_title'] = $current['product_title'];
                            $current['stock']         = 0;
                            $current['price']         = '';
                            $current['ean']           = '';
                            $current['sku']           = '';
                            $current['image']         = '';



                            if (!empty($requiredArray)) {
                                foreach ($requiredArray as $k3 => $v3) {
                                    if (!array_key_exists($v3, $current['attributes'])) {
                                        $current['valid'] = 0;
                                    }
                                }
                            }


                            // Add only such attribute combinations which are possible (two options from select box is not possible)
                            if ($current['valid'] == 1) {
                                $arrayNew[$key] = $current;
                            }

                        }
                    }
                }
            }
        }
        ;
        return $arrayNew;
    }

    public static function getCombinations($id, $title, $attributes, &$combinations = array()) {


        $array         = array();
        $requiredArray = array();


        if (!empty($attributes)) {
            ksort($attributes);
            $i = 0;
            foreach ($attributes as $k => $v) {
                if (!empty($v->options)) {
                    ksort($v->options);
                    foreach ($v->options as $k2 => $v2) {
                        $array[$i]['pid']      = $id;
                        $array[$i]['ptitle']   = $title;
                        $array[$i]['aid']      = $v->id;
                        $array[$i]['atitle']   = $v->title;
                        $array[$i]['oid']      = $v2->id;
                        $array[$i]['otitle']   = $v2->title;
                        $array[$i]['multiple'] = self::isMultipleAttribute($v->type);
                        $array[$i]['required'] = $v->required;
                        $i++;
                    }
                }
                if ($v->required == 1) {
                    $requiredArray[] = $v->id;
                }
            }
        }


        // All combinations of attributes
        $pA = self::makeCombination($array, $requiredArray);

        // Add to the array product itself (product without any variation)
        // Only in case, there is no required attribute
        $pI = array();
        if (empty($requiredArray)) {
            $pIPK                         = $id . '::';
            $pI[$pIPK]['title']           = '(' . Text::_('COM_PHOCACART_NO_ATTRIBUTES') . ')';
            $pI[$pIPK]['cannotcombinate'] = 0;
            $pI[$pIPK]['product_id']      = $id;
            $pI[$pIPK]['product_title']   = $title;
            $pI[$pIPK]['attributes']      = array();
            $pI[$pIPK]['product_key']     = $pIPK;
            $pI[$pIPK]['stock']           = 0;
            $pI[$pIPK]['price']           = '';
            $pI[$pIPK]['sku']               = '';
            $pI[$pIPK]['ean']               = '';
            $pI[$pIPK]['image']               = '';
        }

        $combinations = array_merge($pI, $pA);
        return true;

    }

    public static function getCombinationsDataByProductId($id) {
        if ($id > 0) {
            $db    = Factory::getDBO();
            $query = ' SELECT a.product_id, a.product_key, a.stock, a.price, a.sku, a.ean, a.image'
                . ' FROM #__phocacart_product_stock AS a'
                . ' WHERE a.product_id = ' . (int)$id
                . ' ORDER BY a.id';
            $db->setQuery($query);
            $combinations = $db->loadAssocList();

            $combinationsNew = array();
            if (!empty($combinations)) {
                foreach ($combinations as $k => $v) {
                    $newK                   = $v['product_key'];
                    $combinationsNew[$newK] = $v;
                }
            }
            return $combinationsNew;
        }
        return false;
    }

    public static function getCombinationsDataByKey($productKey) {

        $db = Factory::getDBO();

        $query = 'SELECT product_id, product_key, stock, price, sku, ean, image'
            . ' FROM #__phocacart_product_stock'
            . ' WHERE product_key = ' . $db->quote($productKey)
            . ' ORDER BY product_key'
            . ' LIMIT 1';
        $db->setQuery($query);
        $data = $db->loadAssoc();

        return $data;
    }

    public static function getCombinationsStockByKey($productKey) {

        $db = Factory::getDBO();

        $query = 'SELECT stock'
            . ' FROM #__phocacart_product_stock'
            . ' WHERE product_key = ' . $db->quote($productKey)
            . ' ORDER BY product_key'
            . ' LIMIT 1';
        $db->setQuery($query);
        $stock = $db->loadResult();

        if (isset($stock) && $stock > 0) {
            return $stock;
        }

        return 0;
    }

    public static function getCombinationsPriceByKey($productKey) {

        $db = Factory::getDBO();

        $query = 'SELECT price'
            . ' FROM #__phocacart_product_stock'
            . ' WHERE product_key = ' . $db->quote($productKey)
            . ' ORDER BY product_key'
            . ' LIMIT 1';
        $db->setQuery($query);
        $price = $db->loadResult();

        if (isset($price) && $price > 0) {
            return $price;
        }

        return 0;
    }


    public static function getCombinationsStockById($productId, $returnArray = 0) {

        $db = Factory::getDBO();

        $query = 'SELECT a.id, a.product_id, a.product_key, a.stock, a.attributes'
            . ' FROM #__phocacart_product_stock AS a'
            . ' WHERE a.product_id = ' . (int)$productId
            . ' ORDER BY a.id';
        $db->setQuery($query);
        if ($returnArray) {
            $items = $db->loadAssocList();
        } else {
            $items = $db->loadObjectList();
        }

        return $items;
    }

    public static function storeCombinationsById($productId, $aosArray, $new = 0) {

        if ((int)$productId > 0) {
            $db = Factory::getDBO();


            $notDeleteItems = array();


            if (!empty($aosArray)) {
                $values = array();
                foreach ($aosArray as $k => $v) {

                    if (!is_array($v)) {
                        $v = array();
                    }
                    // correct simple xml
                    if (!isset($v['product_id'])) {
                        $v['product_id'] = '';
                    }
                    if (!isset($v['product_key'])) {
                        $v['product_key'] = '';
                    }
                    if (!isset($v['attributes'])) {
                        $v['attributes'] = '';
                    }
                    if (!isset($v['stock'])) {
                        $v['stock'] = '';
                    }

                    if (empty($v['product_id'])) {
                        $v['product_id'] = '';
                    }
                    if (empty($v['product_key'])) {
                        $v['product_key'] = '';
                    }
                    if (empty($v['attributes'])) {
                        $v['attributes'] = '';
                    }
                    if (empty($v['stock'])) {
                        $v['stock'] = '';
                    }

                    if (empty($v['price'])) {
                        $v['price'] = '';
                    }

                    if (empty($v['sku'])) {
                        $v['sku'] = '';
                    }

                    if (empty($v['ean'])) {
                        $v['ean'] = '';
                    }

                    if (empty($v['image'])) {
                        $v['image'] = '';
                    }

                    if ($v['product_key'] == '') {
                        continue;
                    }

                    $idExists = 0;

                    if ($new == 0) {
                        if (isset($v['id']) && $v['id'] > 0) {

                            // Does the row exist
                            $query = ' SELECT id '
                                . ' FROM #__phocacart_product_stock'
                                . ' WHERE id = ' . (int)$v['id']
                                . ' ORDER BY id';
                            $db->setQuery($query);
                            $idExists = $db->loadResult();

                        }
                    }

                    if ((int)$idExists > 0) {

                        $query = 'UPDATE #__phocacart_product_stock SET'
                            . ' product_id = ' . (int)$productId . ','
                            . ' product_key = ' . $db->quote($v['product_key']) . ','
                            . ' stock = ' . (int)$v['stock'] . ','
                            . ' price = ' . $db->quote($v['price']) . ','
                            . ' sku = ' . $db->quote($v['sku']) . ','
                            . ' ean = ' . $db->quote($v['ean']) . ','
                            . ' image = ' . $db->quote($v['image']) . ','
                            . ' WHERE id = ' . (int)$idExists;
                        $db->setQuery($query);
                        $db->execute();

                        $newIdD = $idExists;

                    } else {

                        $values = '(' . (int)$productId . ', ' . $db->quote($v['product_key']) . ', ' . $db->quote($v['attributes']) . ', ' . (int)$v['stock'] . ', ' . (int)$v['price']. ', ' . (int)$v['sku']. ', ' . (int)$v['ean']. ', '.$db->quote($v['image']).')';


                        $query = ' INSERT INTO #__phocacart_product_stock (product_id, product_key, attributes, stock, price, sku, ean, image)'
                            . ' VALUES ' . $values;
                        $db->setQuery($query);
                        $db->execute();

                        $newIdD = $db->insertid();
                    }

                    $notDeleteItems[] = $newIdD;
                }
            }
            // Remove all discounts except the active
            if (!empty($notDeleteItems)) {
                $notDeleteItemsString = implode(',', $notDeleteItems);
                $query                = ' DELETE '
                    . ' FROM #__phocacart_product_stock'
                    . ' WHERE product_id = ' . (int)$productId
                    . ' AND id NOT IN (' . $notDeleteItemsString . ')';

            } else {
                $query = ' DELETE '
                    . ' FROM #__phocacart_product_stock'
                    . ' WHERE product_id = ' . (int)$productId;
            }
            $db->setQuery($query);
            $db->execute();
        }
    }


    public static function getAttributeType($id) {

        $db       = Factory::getDBO();
        $wheres   = array();
        $wheres[] = ' id = ' . (int)$id;
        $query    = ' SELECT type'
            . ' FROM #__phocacart_attributes'
            . ' WHERE ' . implode(' AND ', $wheres)
            . ' ORDER BY id LIMIT 1';
        $db->setQuery($query);
        $type = $db->loadResult();

        return $type;
    }

    public static function getOptionType($id) {

        $db       = Factory::getDBO();
        $wheres   = array();
        $wheres[] = ' id = ' . (int)$id;
        $query    = ' SELECT type'
            . ' FROM #__phocacart_attribute_values'
            . ' WHERE ' . implode(' AND ', $wheres)
            . ' ORDER BY id LIMIT 1';
        $db->setQuery($query);
        $type = $db->loadResult();

        return $type;
    }

    public static function getAttributeOptionDownloadFilesByOrder($orderId, $productId, $orderProductId) {

        $db       = Factory::getDBO();
        $wheres   = array();
        $wheres[] = ' oa.order_id = ' . (int)$orderId;
        $wheres[] = ' oa.product_id = ' . (int)$productId;
        $wheres[] = ' oa.order_product_id = ' . (int)$orderProductId;

        // only order_option_id not order_attribute_id - as stored is info about ordered option, not about ordered attribute
        $q = ' SELECT av.download_folder, av.download_file, av.download_token, a.id AS attribute_id, av.id AS option_id, oa.id AS order_option_id, av.title AS option_title, a.title AS attribute_title'
            . ' FROM #__phocacart_attribute_values AS av'
            . ' LEFT JOIN #__phocacart_attributes AS a ON a.id = av.attribute_id'
            . ' LEFT JOIN #__phocacart_order_attributes AS oa ON oa.option_id = av.id'
            . ' WHERE ' . implode(' AND ', $wheres)
            . ' ORDER BY av.id';
        $db->setQuery($q);
        $files = $db->loadAssocList();
        return $files;

    }

    /*
     * Done in PhocacartProduct::getProductKey();
     *
    public static function correctArrtibutesFormat(&$attributes) {
        if (!empty($attributes)) {
            foreach ($attributes as $k => $v) {
                if (!is_array($v)) {
                    $attributes[$k] = array($v => $v);
                }
            }
        }
    }*/


    /*	public static function storeOptionsByAttributeId($attributeId, $optArray) {

            if ((int)$attributeId > 0) {
                $db =Factory::getDBO();
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
                        $valuesString = implode(',', $values);

                        $query = ' INSERT INTO #__phocacart_attribute_values (attribute_id, title, operator, amount)'
                                    .' VALUES '.(string)$valuesString;

                        $db->setQuery($query);

                    }
                }
            }
        }

        public static function getAllAttributesSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {

            $db = Factory::getDBO();
            $query = 'SELECT a.id AS value, CONCAT(a.title_attribute,\' (\', a.title,  \')\') AS text'
                    .' FROM #__phocacart_attributes AS a'
                    . ' ORDER BY '. $order;
            $db->setQuery($query);

            $attributes = $db->loadObjectList();

            $attributesO = HTMLHelper::_('select.genericlist', $attributes, $name, 'class="form-control" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);

            return $attributesO;
        }

        */
    public static function getActiveAttributeValues($items, $ordering) {

        $db       = Factory::getDbo();
        $o        = array();
        $wheres   = array();
        $ordering = PhocacartOrdering::getOrderingText($ordering, 5);//at v
        if (!empty($items)) {
            foreach ($items as $k => $v) {
                //$wheres[] = '( v.alias = ' . $db->quote($k) . ' AND at.alias IN (' . $v . ') )';
                $wheres[] = '( '.I18nHelper::sqlCoalesce(['alias'], 'v', '', '', '', '', true).' = ' . $db->quote($k) . ' AND '.I18nHelper::sqlCoalesce(['alias'], 'at', '', '', '', '', true).' IN (' . $v . ') )';
            }
            if (!empty($wheres)) {
                // FULL GROUP BY GROUP_CONCAT(DISTINCT o.title) AS title
                $q = 'SELECT DISTINCT at.title, at.alias, CONCAT(\'a[\', v.alias, \']\')  AS parameteralias, v.title AS parametertitle FROM #__phocacart_attribute_values AS at'
                    . ' LEFT JOIN #__phocacart_attributes AS v ON v.id = at.attribute_id'
                    . (!empty($wheres) ? ' WHERE ' . implode(' OR ', $wheres) : '')
                    . ' GROUP BY v.alias, at.alias, at.title'
                    . ' ORDER BY ' . $ordering;

                $q = 'SELECT DISTINCT '.I18nHelper::sqlCoalesce(['title', 'alias'], 'at')
					.I18nHelper::sqlCoalesce(['alias'], 'v', 'parameter', 'concatparametera', ',')
                    .I18nHelper::sqlCoalesce(['title'], 'v', 'parameter', '', ',')
					. ' FROM #__phocacart_attribute_values AS at'
                    . ' LEFT JOIN #__phocacart_attributes AS v ON v.id = at.attribute_id'
					. I18nHelper::sqlJoin('#__phocacart_attributes_i18n', 'v')
					. I18nHelper::sqlJoin('#__phocacart_attribute_values_i18n', 'at')
                    . (!empty($wheres) ? ' WHERE ' . implode(' OR ', $wheres) : '')
                    . ' GROUP BY v.alias, at.alias, at.title'
                    . ' ORDER BY ' . $ordering;

                $db->setQuery($q);
                $o = $db->loadAssocList();
            }
        }
        return $o;
    }
}

?>
