<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
// In case we don't display the add to cart button
// but we display add to cart icon only - in this case add to cart icon is located outside the add to cart form
// so with add to cart icon we run jQuery event: click on submit button
// but if there is no submit button in form (no add to cart button) we need to add some which will be hidden
?>
<input type="submit" style="display:none" />