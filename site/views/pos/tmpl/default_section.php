<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div id="ph-pc-pos-site">';

// TOP
echo '<div class="ph-pos-wrap-top">';
echo $this->loadTemplate('section_top');
echo '</div>';

echo '<div class="ph-pos-wrap-main">';

echo '<div class="ph-pos-main-page">';

// SECTIONS
echo '<div id="phSections" class="ph-sections">';
echo '<div class="row ">';
if (!empty($this->items)) {

	foreach ($this->items as $k => $v) {
	
		echo '<div class="ph-unit ph-unit-id-'.(int)$v['id'].'">';
		$linkEdit = PhocacartRoute::getPosRoute(1, (int)$v['id'], $this->t['section']->id);
		echo '<div class="ph-pos-section-unit-box"><a class="btn btn-default btn-unit" href="'.$linkEdit.'">'.$v['title'].'</a>';
		if (!empty($v['tickets'])) {
			
			echo '<div class="ph-pos-section-ticket-box">';
			foreach ($v['tickets'] as $k2 => $v2) {
				if ($v2['id'] > 0) {
					
					$linkEditTicket = PhocacartRoute::getPosRoute((int)$v2['id'], (int)$v['unit_id'], (int)$v['section_id']);
					$uO = '';
					$uOClass = '';
					$uOClass = 'ph-pos-ticket-false';
					$uCount = 0;
					if (!empty($v2['cart'])) {
						$cart = unserialize($v2['cart']);
						if (!empty($cart)) {
							$count = 0;
							foreach($cart as $k3 => $v3) {
								if (isset($v3['quantity'])) {
									$count = $count + $v3['quantity'];
								}
							}
							$uOClass = 'ph-pos-ticket-true';
							$uCount = $count;
							if ($count == 1) {
								$uO .= '('.$count.' '.JText::_('COM_PHOCACART_ITEM').')';
							} else if ($count > 1) {
								$uO .= '('.$count.' '.JText::_('COM_PHOCACART_ITEMS').')';
							} else {
								$uOClass = 'ph-pos-ticket-false';
							}
							
							
						}
					}
					echo '<a class="btn btn-ticket '.$uOClass.'" href="'.$linkEditTicket.'" title="'.$uO.'">'.(int)$v2['id'].'<span class="ph-pos-ticket-count '.$uOClass.'">'.$uCount.'</span></a>';
					
				}
			}
			echo '</div>';
		}
		echo '</div>';
		
		
		echo '</div>';
	}
	
} else {
	echo '<div>'.JText::_('COM_PHOCACART_NO_UNIT_FOUND').'</div>';
}

echo '</div>'; // end row
echo '</div>'; // end ph-sections

echo '<div class="ph-pos-hr"></div>';

// SELECT THIS SECTION
echo '<div class="ph-section">';
echo '<div class="row ">';

$linkEdit = PhocacartRoute::getPosRoute(1, 0, $this->t['section']->id);

echo '<div class="ph-unit-section">';
echo '<a class="btn btn-success" href="'.$linkEdit.'">'.JText::_('COM_PHOCACART_SELECT_THIS_SECTION').'</a>';
echo '</div>';

echo '</div>';// end row
echo '</div>';// end ph-section


// Dummy form for javascript which needs the form input values (like current sectionid) - to change the url bar when reseted
echo '<form action="'.$this->t['action'].'" method="post" name="adminForm" id="phPosPaginationBox">'. "\n";
echo '<input type="hidden" name="format" value="raw" />';
echo '<input type="hidden" name="page" value="'.$this->t['page'].'" />';
echo '<input type="hidden" name="ticketid" value="'.$this->t['ticket']->id.'" />';
echo '<input type="hidden" name="unitid" value="'.$this->t['unit']->id.'" />';
echo '<input type="hidden" name="sectionid" value="'.$this->t['section']->id.'" />';
echo JHtml::_( 'form.token' );
echo '</form>';

echo '</div>';// end ph-pos-main-page

echo '</div>';// end ph-pos-wrap-main
	
echo '<div class="ph-pos-wrap-bottom">';
echo $this->loadTemplate('bottom');
echo '</div>';
	
echo '</div>';
?>