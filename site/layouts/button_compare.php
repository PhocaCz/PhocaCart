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
<div class="<?php echo $d['s']['c']['pull-right'] ?>">
    <div class="ph-category-item-compare">
        <form action="<?php echo $d['linkc']; ?>" method="post" id="phCompare<?php echo (int)$d['id']; ?>" class="phItemCompareBoxForm">
            <input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>" />
            <input type="hidden" name="catid" value="<?php echo (int)$d['catid']; ?>" />
            <input type="hidden" name="task" value="comparison.add" />
            <input type="hidden" name="tmpl" value="component" />
            <input type="hidden" name="option" value="com_phocacart" />
            <input type="hidden" name="return" value="<?php echo $d['return']; ?>" />

            <?php if (isset($d['method']) && (int)$d['method'] > 0) { ?>
                <a href="javascript:void(0)" onclick="phItemCompareBoxFormAjax('phCompare<?php echo (int)$d['id']; ?>');" title="<?php echo JText::_('COM_PHOCACART_COMPARE'); ?>"><span class="<?php echo $d['s']['i']['compare'] ?>"></span></a>
            <?php } else { ?>
                <a href="javascript:void(0)" onclick="document.getElementById('phCompare<?php echo (int)$d['id']; ?>').submit();" title="<?php echo JText::_('COM_PHOCACART_COMPARE'); ?>"><span class="<?php echo $d['s']['i']['compare'] ?>"></span></a>
            <?php } ?>

            <?php echo Joomla\CMS\HTML\HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
