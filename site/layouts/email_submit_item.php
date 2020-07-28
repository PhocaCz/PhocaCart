<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d 				= $displayData;
$displayData 	= null;

?>
<p><?php echo JText::_( 'COM_PHOCACART_NEW_ITEM_SUBMITTED' ) ?></p>

<table style="border:0">

	<?php if (isset($d['name']) && $d['name'] != '') { ?>
	<tr style="border:0">
		<td style="border:0"><?php echo JText::_( 'COM_PHOCACART_NAME' ) ?>:</td>
		<td style="border:0"><?php echo $d['name'] ?></td>
	</tr>
	<?php } ?>

	<?php if (isset($d['email']) && $d['email'] != '') { ?>
	<tr style="border:0">
		<td style="border:0"><?php echo JText::_( 'COM_PHOCACART_EMAIL' ) ?>:</td>
		<td style="border:0"><?php echo $d['email'] ?></td>
	</tr>
	<?php } ?>

	<?php if (isset($d['phone']) && $d['phone'] != '') { ?>
	<tr style="border:0">
		<td style="border:0"><?php echo JText::_( 'COM_PHOCACART_PHONE' ) ?>:</td>
		<td style="border:0"><?php echo $d['phone'] ?></td>
	</tr>
	<?php } ?>

    <?php if (isset($d['title']) && $d['title'] != '') { ?>
	<tr style="border:0">
		<td style="border:0"><?php echo JText::_( 'COM_PHOCACART_TITLE' ) ?>:</td>
		<td style="border:0"><?php echo $d['title'] ?></td>
	</tr>
	<?php } ?>

	<tr style="border:0">
		<td style="border:0"><?php echo JText::_( 'COM_PHOCACART_DATE' ) ?>:</td>
		<td style="border:0"><?php echo Joomla\CMS\HTML\HTMLHelper::_('date',  gmdate('Y-m-d H:i:s'), JText::_( 'DATE_FORMAT_LC2' )) ?></td>
	</tr>
</table>

<p><?php echo JText::_( 'COM_PHOCACART_SUBJECT' ) ?>: <b><?php echo $d['subject'] ?></b></p>

<div><?php echo PhocacartUtils::wordDelete($d['message'], $d['numcharemail'], '...') ?></div>

<p>&nbsp;</p>
<p><a href="<?php echo $d['url'] ?>"><?php echo $d['url'] ?></a></p>

<p>&nbsp;</p>
<p><?php echo JText::_( 'COM_PHOCACART_REGARDS' ) ?>,<br><?php echo $d['sitename'] ?></p>

<p>&nbsp;</p>
