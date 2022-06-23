<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if ($this->t['rewards']['apply'] && !$this->t['cartempty']) {


	echo '<div id="ph-request-message" style="display:none"></div>';

	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-reward-points">';

	echo '<div class="' . $this->s['c']['col.xs12.sm12.md12'] . '">';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-inline '.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutRewardPoints">';
	echo '<div class="' . $this->s['c']['form-group'] . '">';

	echo '<input class="'.$this->s['c']['form-control'].' ph-input-sm ph-input-apply-reward-points" type="text" name="phreward" id="phreward" value="'.$this->t['rewards']['usedvalue'].'" >';

	if ($this->t['rewards']['usedvalue'] != '') {
		echo '<button class="' . $this->s['c']['btn.btn-primary'] . ' ph-btn"><span class="' . $this->s['i']['edit'] . '"></span> ' . Text::_('COM_PHOCACART_CHANGE_REWARD_POINTS') . '</button>';
	} else {
		echo '<button class="' . $this->s['c']['btn.btn-primary'] . ' ph-btn"><span class="' . $this->s['i']['save'] . '"></span> ' . Text::_('COM_PHOCACART_APPLY_REWARD_POINTS') . '</button>';
	}


	echo '<input type="hidden" name="task" value="checkout.saverewardpoints" />'. "\n";
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>'. "\n";

	echo '</div>';

	echo '<div class="' . $this->s['c']['col.xs12.sm12.md12'] . '">';
	echo $this->t['rewards']['text'];
	echo '</div>';

	echo '</div>';

	echo '</div>' . "\n";// end box action



}

?>
