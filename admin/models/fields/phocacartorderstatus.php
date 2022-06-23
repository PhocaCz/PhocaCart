<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

class JFormFieldPhocacartOrderstatus extends ListField
{
	protected $type 		= 'PhocacartOrderstatus';

	protected function getInput() {

		$javascript	= '';
		$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		$multiple	= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$type 		= isset($this->element['typemethod']) ? (int)$this->element['typemethod'] : 0;



		$attr		= '';
		$attr		.= 'class="form-select" ';
		if ($multiple) {
			$attr		.= 'size="4" multiple="multiple" ';
		}
		if ($required) {
			$attr		.= 'required aria-required="true" ';
		}

		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		$attr		.= $javascript . ' ';

		if ($multiple) {

			$db = Factory::getDBO();

			$query = 'SELECT a.title AS text, a.id AS value'
			. ' FROM #__phocacart_order_statuses AS a'
			. ' WHERE a.published = 1'
			. ' ORDER BY a.ordering';
			$db->setQuery( $query );
			$datas = $db->loadObjectList();
			if (!empty($datas)) {
				foreach ($datas as $k => $v) {
					$datas[$k]->text = Text::_($v->text);
				}
			}
			array_unshift($datas, HTMLHelper::_('select.option', '0', Text::_('COM_PHOCACART_NONE'), 'value', 'text'));
			array_unshift($datas, HTMLHelper::_('select.option', '-1', Text::_('COM_PHOCACART_ALL'), 'value', 'text'));



			$data               = $this->getLayoutData();
			$data['options']    = (array)$datas;
			$data['value']      = $this->value;

			return $this->getRenderer($this->layout)->render($data);


			//return HTMLHelper::_('select.genericlist',  $datas,  $this->name, $attr, 'value', 'text', $this->value, $this->id );

		} else {
			$id = (int) $this->form->getValue('status_id');

			if ($id < 1) {
				$id = 1;// set default "pending"
			}

			$attr .= ' class="form-select"';

			$status = PhocacartOrderStatus::getStatus($id);
			if ($type == 1) {
                array_unshift($status['data'], HTMLHelper::_('select.option', 0, Text::_('COM_PHOCACART_NO'), 'value', 'text'));
            } else if ($type == 2) {
				array_unshift($status['data'], HTMLHelper::_('select.option', '', ' - ' . Text::_('COM_PHOCACART_OPTION_SELECT_ORDER_STATUS') . ' - ', 'value', 'text'));
			}
			return HTMLHelper::_('select.genericlist',  $status['data'],  $this->name, $attr , 'value', 'text', $this->value, $this->id );
		}
	}
}
?>
