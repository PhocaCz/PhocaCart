<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d = $displayData;
?>
<div class="<?php echo $d['class']; ?>">
<?php if (isset($d['priceitemsorig']['bruttoformat']) && $d['priceitemsorig']['bruttoformat']) { ?>
	<div class="ph-price-txt"><?php echo JText::_('COM_PHOCACART_ORIGINAL_PRICE') ?></div>
	<div class="ph-price-original"><?php echo $d['priceitemsorig']['bruttoformat'] ?></div>
<?php } ?>

<?php /*
if ($d['priceitemsorig']['brutto'] > $d['priceitems']['brutto']) {
	if (isset($d['priceitemsorig']['bruttoformat']) && $d['priceitemsorig']['bruttoformat']) { ?>
		<div class="ph-price-txt"><?php echo JText::_('COM_PHOCACART_ORIGINAL_PRICE') ?></div>
		<div class="ph-price-original"><?php echo $d['priceitemsorig']['bruttoformat'] ?></div>
<?php }
} */ ?>

<?php if ($d['priceitems']['netto'] && $d['priceitems']['taxcalc'] > 0) { ?>
	<div class="ph-price-txt"><?php echo $d['priceitems']['nettotxt'] ?></div>
	<div class="ph-price-netto"><?php echo $d['priceitems']['nettoformat'] ?></div>
<?php } ?>

<?php if ($d['priceitems']['tax'] && $d['priceitems']['taxcalc'] > 0) { ?>
	<div class="ph-tax-txt"><?php echo $d['priceitems']['taxtxt'] ?></div>
	<div class="ph-tax"><?php echo $d['priceitems']['taxformat'] ?></div>
<?php } ?>

<?php if ($d['priceitems']['brutto']) { ?>
	<div class="ph-price-txt"><?php echo $d['priceitems']['bruttotxt'] ?></div>
	<div class="ph-price-brutto"><?php echo $d['priceitems']['bruttoformat'] ?></div>
<?php } ?>

<?php if ($d['priceitems']['baseformat']) { ?>
	<div class="ph-price-txt"><?php echo JText::_('COM_PHOCACART_UNIT_PRICE') ?></div>
	<div class="ph-price-base"><?php echo $d['priceitems']['baseformat'] ?></div>
<?php } ?>

	<div class="ph-cb"></div>
</div>
<div class="ph-cb"></div>