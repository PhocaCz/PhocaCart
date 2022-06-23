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
$d = $displayData;

echo '<div class="'.$d['s']['c']['pull-right'].'">';

if ($d['display_view_category_button'] == 1) {

    echo '<a href="'.$d['link'].'" class="'.$d['s']['c']['btn.btn-primary'].'" role="button">';
    echo '<span class="'.$d['s']['i']['view-category'].'"></span> ';
    echo Text::_('COM_PHOCACART_VIEW_CATEGORY').'</a>';

} else if ($d['display_view_category_button'] == 2) {

    echo '<a href="'.$d['link'].'" class="'.$d['s']['c']['btn.btn-primary'].'" role="button" title="'.Text::_('COM_PHOCACART_VIEW_CATEGORY').'">';
    echo '<span class="'.$d['s']['i']['view-category'].'"></span></a>';

}

echo '</div>';
echo '<div class="ph-cb"></div>';
?>
