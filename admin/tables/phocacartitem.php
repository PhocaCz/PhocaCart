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
jimport('joomla.filter.input');
use Joomla\String\StringHelper;

class TablePhocaCartItem extends Table
{
	protected $_jsonEncode = array('params', 'metadata');

	function __construct(& $db) {
		parent::__construct('#__phocacart_products', 'id', $db);
	}

	public function bind($src, $ignore = [])
	{
		if (!\is_array($ignore)) {
			$ignore = explode(' ', $ignore);
		}
		$ignore[] = 'discount_percent';

		return parent::bind($src, $ignore);
	}

	function check() {

		if (trim( $this->title ) == '') {
			$this->setError( Text::_( 'COM_PHOCACART_PRODUCT_MUST_HAVE_TITLE') );
			return false;
		}

		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocacartUtils::getAliasName($this->alias);

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

		return true;
	}
}
?>
