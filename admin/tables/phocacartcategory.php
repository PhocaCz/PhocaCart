<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;
jimport('joomla.filter.input');
use Joomla\String\StringHelper;

class TablePhocacartCategory extends Table
{

	protected $_jsonEncode = array('params', 'metadata');

	function __construct(& $db) {
		parent::__construct('#__phocacart_categories', 'id', $db);
	}

	function check() {
		if (trim( $this->title ) == '') {
			$this->setError( Text::_( 'COM_PHOCACART_CATEGORY_MUST_HAVE_TITLE') );
			return false;
		}

		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocacartUtils::getAliasName($this->alias);

        // Add prefix to category alias if it or its name starts with digit
        // Needed, when: SUBCATEGORY is used, SUBCATEGORY NAME starts with digit, SEF is used because of possible problems in router
        // Digit in subcategory name can be handled as product ID because two Numbers will be displayed in URL (first real subcategory ID, second digit in alias title which will confuse router
		$pC = PhocacartUtils::getComponentParameters();
		$category_alias_prefix = $pC->get('category_alias_prefix', '');
        if ($category_alias_prefix != '' && is_numeric(substr($this->alias, 0, 1))) {
            $this->alias = ApplicationHelper::stringURLSafe($category_alias_prefix . $this->alias);
        }





		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string if not empty
		if (!empty($this->metakey))
		{
			// Array of characters to remove
			$bad_characters = array("\n", "\r", "\"", '<', '>');

			// Remove bad characters
			$after_clean = StringHelper::str_ireplace($bad_characters, '', $this->metakey);

			// Create array using commas as delimiter
			$keys = explode(',', $after_clean);
			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(', ', $clean_keys);
		}

		// Clean up description -- eliminate quotes and <> brackets
		if (!empty($this->metadesc))
		{
			// Only process if not empty
			$bad_characters = array("\"", '<', '>');
			$this->metadesc = StringHelper::str_ireplace($bad_characters, '', $this->metadesc);
		}

		if (!isset($this->modified) || $this->modified == '0' || $this->modified == '') {
			$this->modified = '0000-00-00 00:00:00';
		}
		if (!isset($this->count_date) || $this->count_date == '0' || $this->count_date == '') {
			$this->count_date = '0000-00-00 00:00:00';
		}
		if (!isset($this->created) || $this->created == '0' || $this->created == '') {
			$this->created = '0000-00-00 00:00:00';
		}

		return true;
	}
}
?>
