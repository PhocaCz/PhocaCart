<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

// @deprecated 4.0 the function parameter, the inline js and the buttons are not needed since 3.7.0.
$function  = Factory::getApplication()->input->getCmd('function', 'jEditPhocacartmanufacturer_' . (int) $this->item->id);

// Function to update input title when changed
// phocacartmanufacturer-form => adminForm
Factory::getDocument()->addScriptDeclaration('
	function jEditPhocacartmanufacturerModal() {
		if (window.parent && document.formvalidator.isValid(document.getElementById("adminForm"))) {
			return window.parent.' . $this->escape($function) . '(document.getElementById("jform_title").value);
		}
	}
');
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('phocacartmanufacturer.apply'); jEditPhocacartmanufacturerModal();"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('phocacartmanufacturer.save'); jEditPhocacartmanufacturerModal();"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('phocacartmanufacturer.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
