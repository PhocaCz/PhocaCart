<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die();
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldPhocacartOrdering extends JFormField
{

	protected $type = 'PhocacartOrdering';

	protected function getInput() {
		// Initialize variables.
		$html = array();
		$attr = '';

		// Get some field values from the form.
		$id			= (int) $this->form->getValue('id');

		if ($this->element['table']) {
			switch (strtolower($this->element['table'])) {

				case "payment":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_payment_methods';
				break;

				case "status":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_order_statuses';
				break;

				case "stockstatus":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_stock_statuses';
				break;

				case "country":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_countries';
				break;

				case "region":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_regions';
				break;

				case "currency":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_currencies';
				break;

				case "tag":
                case "label":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_tags';
				break;

				case "manufacturer":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_manufacturers';
				break;

				case "shipping":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_shipping_methods';
				break;

				case "attribute":
				default:
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_attributes';
				break;

				case "formfield":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_form_fields';
				break;

				case "user":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_users';
				break;

				case "order":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_orders';
				break;

				case "review":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_reviews';
				break;

				case "question":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_questions';
				break;

				case "submititem":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_submit_items';
				break;

				case "wishlist":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocacart_wishlists';
				break;

                case "category":
                    $whereLabel	=	'';
                    $whereValue	=	'';
                    $table		=	'#__phocacart_categories';
                break;

			}
		} else {
			$whereLabel	=	'catid';
			$whereValue	=	(int) $this->form->getValue('catid');
			$table		=	'#__phocacart';
		}

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';


		// Build the query for the ordering list.
		$query = 'SELECT ordering AS value, title AS text' .
				' FROM ' . $table;
		if ($whereLabel != '') {
			$query .= ' WHERE '.$whereLabel.' = ' . (int) $whereValue;
		}
		$query .= ' ORDER BY ordering';

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true') {
			$html[] = Joomla\CMS\HTML\HTMLHelper::_('list.ordering', '', $query, trim($attr), $this->value, $id ? 0 : 1);
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
		}
		// Create a regular list.
		else {
			$html[] = Joomla\CMS\HTML\HTMLHelper::_('list.ordering', $this->name, $query, trim($attr), $this->value, $id ? 0 : 1);

		}



		return implode($html);
	}
}
