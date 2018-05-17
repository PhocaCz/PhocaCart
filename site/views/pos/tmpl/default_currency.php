<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;


$currList 	= array();
$currList[] = '<div class="ph-currency-list-box">';
if (!empty($this->t['currency_array'])) {
	foreach($this->t['currency_array'] as $k => $v) {
		$image = '';
		
		if (isset($v->image) && $v->image != '') {
			$image = '<img class="ph-currency-image-list" src="'.JURI::base(true). '/' . $v->image.'" ale="'.$v->code.'" />';
		}
		
		if ($v->active == 1) {
			//$item .= ' <span class="ph-currency-list-suffix">('.$image .' ' . $v->code.')</span>';
			//$item .= ' <span class="ph-currency-list-suffix">'.$image .' ' . $v->code.'</span>';
		}
		
		$currList[] = '<a href="javascript:void(0);" onclick="jQuery(\'<input>\').attr({type: \'hidden\', id: \'id\', name: \'id\', value: \''.(int)$v->id.'\'}).appendTo(\'#phPosCurrencyBoxForm\');jQuery(\'#phPosCurrencyBoxForm\').submit()" class="btn btn-info ph-btn-dropdown-currency"><span class="ph-currency-list">'.$image .'</span> '. $v->text.'</a>';
	
	}
}
$currList[] = '</div>';

echo '<div>';
echo implode('', $currList);
echo '<form action="'.$this->t['linkcheckout'].'" method="post" id="phPosCurrencyBoxForm">';
echo '<input type="hidden" name="task" value="checkout.currency">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
echo JHtml::_('form.token');
echo '</form>';
echo '</div>';