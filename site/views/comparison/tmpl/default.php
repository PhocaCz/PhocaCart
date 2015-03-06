<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div id="ph-pc-comparison-box" class="pc-comparison-view'.$this->p->get( 'pageclass_sfx' ).'">';
if ( $this->p->get( 'show_page_heading' ) ) { 
	echo '<h1>'. $this->escape($this->p->get('page_heading')) . '</h1>';
} else {
	echo '<h1>'. JText::_('COM_PHOCACART_COMPARISON'). '</h1>';
}





if (!empty($this->t['items'])) {

	$c = array();
	$c['title']		= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_PRODUCT').'</b></td>';
	$c['price']		= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_PRICE').'</b></td>';
	$c['remove'] 	= '<tr><td class="ph-middle"></td>';
	$c['desc']		= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_DESCRIPTION').'</b></td>';
	$c['man'] 		= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_MANUFACTURER').'</b></td>';

	if ($this->t['value']['stock'] == 1)	{ $c['stock'] 	= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_AVAILABILITY').'</b></td>';}

	if ($this->t['value']['length'] == 1)	{ $c['length'] 	= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_LENGTH').'</b></td>';}
	if ($this->t['value']['width'] == 1)	{ $c['width'] 	= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_WIDTH').'</b></td>';}
	if ($this->t['value']['height'] == 1) 	{ $c['height'] 	= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_HEIGHT').'</b></td>';}
	if ($this->t['value']['weight'] == 1) 	{ $c['weight'] 	= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_WEIGHT').'</b></td>';}
	if ($this->t['value']['volume'] == 1) 	{ $c['volume'] 	= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_VOLUME').'</b></td>';}

	
	if ($this->t['value']['attrib'] == 1) 	{ $c['attrib'] 	= '<tr><td class="ph-middle"><b>'.JText::_('COM_PHOCACART_ATTRIBUTES').'</b></td>';}
	

	$count = count($this->t['items']);
	$price = new PhocaCartPrice();

	foreach($this->t['items'] as $k => $v) {
		
		
		$c['title'] .= '<td><h3>'.$v['title'].'</h3>';
		$image = PhocaCartImage::getThumbnailName($this->t['pathitem'], $v['image'], 'small');
		if (isset($image->rel) && $image->rel != '') {
			$c['title'] .= '<div class="ph-center" ><img class="ph-center" src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive img-thumbnail ph-image-full" /></div>';
		}
		$c['title'] .= '</td>';
		
		$c['price'] .= '<td class="ph-right">'.$price->getPriceFormat($v['price']).'</td>';
		
		$c['remove'] .= '<td>';
		$c['remove'] .= '<form action="'.$this->t['linkcomparison'].'" method="post">';
		$c['remove'] .= '<input type="hidden" name="id" value="'.(int)$v['id'].'">';
		$c['remove'] .= '<input type="hidden" name="task" value="comparison.remove">';
		$c['remove'] .= '<input type="hidden" name="tmpl" value="component" />';
		$c['remove'] .= '<input type="hidden" name="option" value="com_phocacart" />';
		$c['remove'] .= '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
		$c['remove'] .= '<div class="ph-center">';
		$c['remove'] .= '<button type="submit" class="btn btn-primary ph-btn" role="button"><span class="glyphicon glyphicon-remove"></span> '.JText::_('COM_PHOCACART_REMOVE').'</button>';
		$c['remove'] .= '</div>';
		$c['remove'] .= JHtml::_('form.token');
		$c['remove'] .= '</form>';
		$c['remove'] .= '</td>';
		
		$c['desc'] .= '<td>'.$v['description'].'</td>';
		$c['man'] .= '<td class="ph-center">'.$v['manufacturer_title'].'</td>';
		
		if ($this->t['value']['stock'] == 1)	{ $c['stock'] 	.= '<td class="ph-center">'.$v['stock'].'</td>';}
		
		if ($this->t['value']['length'] == 1)	{ $c['length'] 	.= '<td class="ph-center">'.$v['length'].' '.$this->t['unit_size'].'</td>';}
		if ($this->t['value']['width'] == 1)	{ $c['width'] 	.= '<td class="ph-center">'.$v['width'].' '.$this->t['unit_size'].'</td>';}
		if ($this->t['value']['height'] == 1)	{ $c['height'] 	.= '<td class="ph-center">'.$v['height'].' '.$this->t['unit_size'].'</td>';}
		if ($this->t['value']['weight'] == 1)	{ $c['weight'] 	.= '<td class="ph-center">'.$v['weight'].' '.$this->t['unit_weight'].'</td>';}
		if ($this->t['value']['volume'] == 1)	{ $c['volume'] 	.= '<td class="ph-center">'.$v['volume'].' '.$this->t['unit_volume'].'</td>';}
		
		if ($this->t['value']['attrib'] == 1) 	{ 
			$c['attrib'] 	.= '<td>';
			if(!empty($v['attr_options'])) {
				foreach ($v['attr_options'] as $k2 => $v2) {
					$c['attrib'] 	.= '<div>'.$v2->title.'</div>';
					if(!empty($v2->options)) {
						$c['attrib'] 	.= '<ul>';
						foreach ($v2->options as $k3 => $v3) {
							$c['attrib'] 	.= '<li>'.$v3->title.'</li>';
						}
						$c['attrib'] 	.= '</ul>';
					}
				}
			
			}
			$c['attrib'] 	.= '</td>';
		}
		
	
	
	}
	
	$c['title'] .= '</tr>';
	$c['price'] .= '</tr>';
	$c['desc'] .= '</tr>';
	$c['man'] .= '</tr>';
	$c['remove'] .= '</tr>';

	if ($this->t['value']['stock'] == 1)	{ $c['stock'] 	.= '</tr>';}
	
	if ($this->t['value']['length'] == 1)	{ $c['length'] 	.= '</tr>';}
	if ($this->t['value']['width'] == 1)	{ $c['width'] 	.= '</tr>';}
	if ($this->t['value']['height'] == 1) 	{ $c['height'] 	.= '</tr>';}
	if ($this->t['value']['weight'] == 1) 	{ $c['weight'] 	.= '</tr>';}
	if ($this->t['value']['volume'] == 1) 	{ $c['volume'] 	.= '</tr>';}

	if ($this->t['value']['attrib'] == 1) 	{ $c['attrib'] 	.= '</tr>';}


	echo '<div class="ph-comparison-items">';
	echo '<table class="ph-comparison-table">';
	foreach($c as $k => $v) {
		echo $v;
	}
	foreach($this->t['spec'] as $k => $v) {
		if($k != '') {
			echo '<tr><td><b><u>'.$k.'</u></b></td><td colspan="'.$count.'"></td></tr>';
			if (!empty($v)) {
				foreach($v as $k2 => $v2) {
					echo '<tr><td><b>'.$k2.'</b></td>';
					
					if ($count == 1) {
						if (isset($v2[0])) { echo '<td class="ph-center">'.$v2[0].'</td>';} else {echo '<td></td>';}
					} else if ($count == 2) {
						if (isset($v2[0])) { echo '<td class="ph-center">'.$v2[0].'</td>';} else {echo '<td></td>';}
						if (isset($v2[1])) { echo '<td class="ph-center">'.$v2[1].'</td>';} else {echo '<td></td>';}
					} else {
						if (isset($v2[0])) { echo '<td class="ph-center">'.$v2[0].'</td>';} else {echo '<td></td>';}
						if (isset($v2[1])) { echo '<td class="ph-center">'.$v2[1].'</td>';} else {echo '<td></td>';}
						if (isset($v2[2])) { echo '<td class="ph-center">'.$v2[2].'</td>';} else {echo '<td></td>';}
					}
					
					echo'</tr>';
				}
			}
		}
	}
	echo '</table>';
	echo '</div>';// end comparison items
} else {
	echo '<div class="alert alert-error alert-danger">'.JText::_('COM_PHOCACART_THERE_ARE_NO_PRODUCTS_IN_COMPARISON_LIST').'</div>';
}



echo '</div>';// end comparison box
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>