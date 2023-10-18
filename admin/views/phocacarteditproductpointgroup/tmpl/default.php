<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
HTMLHelper::_('formbehavior.chosen', 'select');


$link		= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditproductpointgroup&tmpl=component&id='.(int)$this->id);


echo '<div id="phAdminEditPopup" class="ph-edit-stock-advanced-box">';

echo '<div class="alert alert-info alert-dismissible fade show" role="alert">'
	. '<ul><li>'.Text::_('COM_PHOCACART_TO_SEE_ALL_CUSTOMER_GROUPS_LISTED_CLOSE_WINDOW_SAVE_THE_PRODUCT_FIRST') . '</li>'
	. '<li>'. Text::_('COM_PHOCACART_CHECK_LIST_EVERY_TIME_CUSTOMER_GROUPS_CHANGE').'</li>'
	. '<li>'.Text::_('COM_PHOCACART_IF_YOU_SET_ZERO_AS_POINT_THEN_POINT_WILL_BE_ZERO').'</li>'
	.'</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="'.Text::_('COM_PHOCACART_CLOSE').'"></button></div>';

if (!empty($this->t['product'])) {

	echo '<div class="ph-product-customer-group-box">';

	echo '<form action="'.$link.'" method="post">';


	if (!empty($this->t['groups'])) {

		echo '<table class="ph-product-customer-group-box">';

		echo '<tr>';
		echo '<th>'.Text::_('COM_PHOCACART_CUSTOMER_GROUP').'</th>';
		//echo '<th>'.Text::_('COM_PHOCACART_PRODUCT_KEY').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_POINTS_RECEIVED').'</th>';
		echo '</tr>';


	/*	// Default is the main price
		echo '<tr>';
		echo '<td>'.Text::_('COM_PHOCACART_DEFAULT').'</td>';
		echo '<td><input type="text" class="input-small" name="jformdefault[]" value="'.$this->t['product']->points_received.'" readonly />';
		echo '</td>';
		echo '</tr>';*/

		foreach($this->t['groups'] as $k => $v) {

			echo '<tr>';
			echo '<td>'.Text::_($v['title']).'</td>';

			if ($v['type'] == 1) {
				//continue;
				// Default


				echo '<td><input type="text" class="input-small form-control" name="jform['.$v['id'].'][points_received]" value="'.$this->t['product']->points_received.'" readonly  />';
				echo '<input type="hidden" name="jform['.$v['id'].'][group_id]" value="'.$v['id'].'" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$this->id.'" />';

			} else {




				// Set value from database
				$points = '';
				if (isset($this->t['product_groups'][$v['id']]['points_received'])) {
					$points = $this->t['product_groups'][$v['id']]['points_received'];
				}
				echo '<td><input type="text" class="input-small form-control" name="jform['.$v['id'].'][points_received]" value="'.$points.'" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][group_id]" value="'.$v['id'].'" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$this->id.'" />';
				//echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$v['product_id'].'" />';
				//echo '<input type="hidden" name="jform['.$v['id'].'][attributes]" value="'.serialize($v['attributes']).'" />';
			}
			echo '</td>';
			echo '</tr>';

		}

		echo '<tr><td colspan="2"></td></tr>';

		echo '<tr>';
		echo '<td></td>';

		echo '<td>';
		echo '<input type="hidden" name="id" value="'.(int)$this->t['product']->id.'">';
		echo '<input type="hidden" name="task" value="phocacarteditproductpointgroup.save">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<button class="btn btn-success btn-sm ph-btn"><span class="icon-ok ph-icon-white"></span> '.Text::_('COM_PHOCACART_SAVE').'</button>';
		echo HTMLHelper::_('form.token');


		echo '</tr>';

		echo '</table>';
	}
}

?>
