<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;


echo PhocacartTicket::renderNavigation($this->t['vendor']->id, $this->t['ticket']->id, $this->t['unit']->id, $this->t['section']->id);
echo PhocacartSection::renderTitleAndBackButton($this->t['section']->id, $this->t['unit']->id);

echo '<div class="ph-add-remove-tickets">';
echo '<form id="phPosAddTicketForm" class="form-inline" style="display:inline" action="'.$this->t['action'].'" method="post">';

echo '<button class="'.$this->s['c']['btn.btn-success'].' ph-pos-btn-ticket" />';
//echo '<span class="'.$this->s['i']['plus'].' icon-white"></span>';
echo PhocacartRenderIcon::icon($this->s['i']['plus'] . ' icon-white');
echo '</button>';
echo '<input type="hidden" name="task" value="pos.addticket">';
echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'">';
echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
echo HTMLHelper::_('form.token');
echo '</form>';

$msg = Text::_('COM_PHOCACART_TICKET_NR') . ': <b>'.(int)$this->t['ticket']->id . '</b><br />'
	. Text::_('COM_PHOCACART_WARNING_CLOSE_CURRENT_TICKET') . '<br />'
	. '<span class="ph-warning">'.Text::_('COM_PHOCACART_WARNING_CART_WILL_BE_CLOSED_ALL_DATA_WILL_BE_REMOVED') . '</span>';

echo '<form id="phPosCloseTicketForm" class="form-inline" style="display:inline" action="'.$this->t['action'].'" method="post" data-txt="'.htmlspecialchars($msg).'">';
echo '<button class="'.$this->s['c']['btn.btn-danger'].' ph-pos-btn-ticket" >';
//echo '<span class="'.$this->s['i']['minus'].' icon-white"></span>'
echo PhocacartRenderIcon::icon($this->s['i']['minus'] . ' icon-white');
echo '</button>';
echo '<input type="hidden" name="task" value="pos.removeticket">';
echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'">';
echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'">';
echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
echo HTMLHelper::_('form.token');
echo '</form>';

echo '</div>';// end ph-add-remove-tickets


echo $this->loadTemplate('vendor');
echo $this->loadTemplate('logo');
?>
