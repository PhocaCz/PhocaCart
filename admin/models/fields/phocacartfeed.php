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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


class JFormFieldPhocacartFeed extends FormField
{
	protected $type 		= 'PhocacartFeed';

	protected function getInput() {

		$db = Factory::getDBO();

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_feeds AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();


	//	$view 	= Factory::getApplication()->input->get( 'view' );

		//$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;

		$attr = '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= ' class="form-select"';

		array_unshift($data, HTMLHelper::_('select.option', '', '- '.Text::_('COM_PHOCACART_SELECT_XML_FEED').' -', 'value', 'text'));
		return HTMLHelper::_('select.genericlist',  $data,  $this->name, trim($attr), 'value', 'text', $this->value, $this->id );
	}
}
?>
