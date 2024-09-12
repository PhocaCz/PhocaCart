<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;

class JFormFieldPhocaEditor extends FormField
{

	public $type = 'PhocaEditor';
	protected $editor;

	protected function getInput(){


		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . ' mceEditor"' : '';

		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$columns = $this->element['cols'] ? ' cols="' . (int) $this->element['cols'] . '"' : '';
		$rows = $this->element['rows'] ? ' rows="' . (int) $this->element['rows'] . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$height      = ((string) $this->element['height']) ? (string) $this->element['height'] : '250';
		$width       = ((string) $this->element['width']) ? (string) $this->element['width'] : '100%';
		$assetField  = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset       = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];

		// Build the buttons array.
		$buttons = (string) $this->element['buttons'];

		if ($buttons == 'true' || $buttons == 'yes' || $buttons == '1')
		{
			$buttons = true;
		}
		elseif ($buttons == 'false' || $buttons == 'no' || $buttons == '0')
		{
			$buttons = false;
		}
		else
		{
			$buttons = explode(',', $buttons);
		}

		$hide = ((string) $this->element['hide']) ? explode(',', (string) $this->element['hide']) : array();

		// We search for defined editor (tinymce)
		$editor = $this->getEditor();
		if ($editor) {
			$js =	'<script type="text/javascript">' . "\n";
			$js .= 	 'tinyMCE.init({'. "\n"
						.'mode : "textareas",'. "\n"
						.'theme : "advanced",'. "\n"
						.'language : "en",'. "\n"
						.'plugins : "emotions",'. "\n"
						.'editor_selector : "mceEditor",'. "\n"
						.'theme_advanced_buttons1 : "bold, italic, underline, separator, strikethrough, justifyleft, justifycenter, justifyright, justifyfull, bullist, numlist, undo, redo, link, unlink, separator, emotions",'. "\n"
						.'theme_advanced_buttons2 : "",'. "\n"
						.'theme_advanced_buttons3 : "",'. "\n"
						.'theme_advanced_toolbar_location : "top",'. "\n"
						.'theme_advanced_toolbar_align : "left",'. "\n";
			//if ($displayPath == 1) {
				$js .= 'theme_advanced_path_location : "bottom",'. "\n";
			//}
			$js .=		 'extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
	});' . "\n";
			$js .=	'</script>';

			$js2 = "\t<script type=\"text/javascript\" src=\"".Uri::root()."media/editors/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n";


			$js = '<script type="text/javascript">
				tinyMCE.init({
					// General
					//directionality: "ltr",
					//language : "en",
					menubar:false,
					statusbar: false,
					mode : "specific_textareas",
					skin : "lightgray",
					theme : "modern",
					schema: "html5",
					selector: "textarea.mce_editable",
					// Cleanup/Output
					inline_styles : true,
					gecko_spellcheck : true,
					entity_encoding : "raw",
					extended_valid_elements : "hr[id|title|alt|class|width|size|noshade]",
					force_br_newlines : false, force_p_newlines : true, forced_root_block : \'p\',
					toolbar_items_size: "small",
					invalid_elements : "script,applet,iframe",
					// Plugins
					plugins : "link image autolink lists",
					// Toolbar
					toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | undo redo | link unlink anchor image",
					removed_menuitems: "newdocument",
					// URL
					relative_urls : true,
					remove_script_host : false,
					document_base_url : "'.Uri::base().'",
					// Layout
					content_css : "'.Uri::base().'templates/system/css/editor.css",
					//importcss_append: true,
					// Advanced Options
					resize: "both",
					//height : "550",
					//width : "750",

				});
				</script>';

			$js2 = "\t<script type=\"text/javascript\" src=\"".Uri::root()."media/editors/tinymce/tinymce.min.js\"></script>\n";


			$document	= Factory::getDocument();
			$document->addCustomTag($js2);
			$document->addCustomTag($js);

			if (is_numeric( $width )) {
				$width .= 'px';
			}
			if (is_numeric( $height )) {
				$height .= 'px';
			}

			// Problem with required
			$class = str_replace('required', '', $class);

			$editor = '<textarea class="mce_editable" name="' . $this->name . '" id="' . $this->id . '"' . $columns . $rows . $class . $disabled . $onchange . ' style="width:' . $width .'; height:'. $height.'">'
				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
		} else {
			$editor = '<textarea class="mce_editable" name="' . $this->name . '" id="' . $this->id . '"' . $columns . $rows . $class . $disabled . $onchange . ' style="width:' . $width .'; height:'. $height.'">'
				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
		}
		return $editor;
	}

	/**
	 * Method to get a JEditor object based on the form field.
	 *
	 * @return  JEditor  The JEditor object.
	 *
	 * @since   1.6
	 */
	protected function getEditor()
	{
		// Only create the editor if it is not already created.
		if (empty($this->editor))
		{
			$editor = null;

			// Get the editor type attribute. Can be in the form of: editor="desired|alternative".
			$type = trim((string) $this->element['editor']);

			if ($type)
			{
				// Get the list of editor types.
				$types = explode('|', $type);

				// Get the database object.
				$db = Factory::getDBO();

				// Iterate over teh types looking for an existing editor.
				foreach ($types as $element)
				{
					// Build the query.
					$query = $db->getQuery(true);
					$query->select('element');
					$query->from('#__extensions');
					$query->where('element = ' . $db->quote($element));
					$query->where('folder = ' . $db->quote('editors'));
					$query->where('enabled = 1');

					// Check of the editor exists.
					$db->setQuery($query, 0, 1);
					$editor = $db->loadResult();

					// If an editor was found stop looking.
					if ($editor)
					{
						break;
					}
				}
			}

			// Create the JEditor instance based on the given editor.
			if (is_null($editor))
			{
				$conf = Factory::getConfig();
				$editor = $conf->get('editor');
			}
			//PHOCAEDIT
			if ($editor != trim((string) $this->element['editor'])) {
				return false;
			}
			// END PHOCAEDIT

			$this->editor = Editor::getInstance($editor);
		}

		return $this->editor;
	}

	public function save()
	{
		return $this->getEditor()->save($this->id);
	}
}
