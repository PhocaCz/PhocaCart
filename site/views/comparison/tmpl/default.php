<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

$layoutP	= new FileLayout('product_price', null, array('component' => 'com_phocacart'));
$layoutI	= new FileLayout('image', null, array('component' => 'com_phocacart'));
$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));

echo '<div id="ph-pc-comparison-box" class="pc-view pc-comparison-view'.$this->p->get( 'pageclass_sfx' ).'">';


echo PhocacartRenderFront::renderHeader(array(Text::_('COM_PHOCACART_COMPARISON')));


if (!empty($this->t['items'])) {

	$c = array();
	$c['title']		= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_PRODUCT').'</b></td>';

	if ($this->t['can_display_price']) {
		$c['price']		= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_PRICE').'</b></td>';
	}
	$c['remove'] 	= '<tr><td class="ph-middle"></td>';
	$c['desc']		= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_DESCRIPTION').'</b></td>';
	$c['man'] 		= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_MANUFACTURER').'</b></td>';
	$c2['link'] 	= '<tr><td></td>';

	if ($this->t['value']['stock'] == 1)	{ $c['stock'] 	= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_AVAILABILITY').'</b></td>';}

	if ($this->t['value']['length'] == 1)	{ $c['length'] 	= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_LENGTH').'</b></td>';}
	if ($this->t['value']['width'] == 1)	{ $c['width'] 	= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_WIDTH').'</b></td>';}
	if ($this->t['value']['height'] == 1) 	{ $c['height'] 	= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_HEIGHT').'</b></td>';}
	if ($this->t['value']['weight'] == 1) 	{ $c['weight'] 	= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_WEIGHT').'</b></td>';}
	if ($this->t['value']['volume'] == 1) 	{ $c['volume'] 	= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_VOLUME').'</b></td>';}


	if ($this->t['value']['attrib'] == 1) 	{ $c['attrib'] 	= '<tr><td class="ph-middle"><b>'.Text::_('COM_PHOCACART_ATTRIBUTES').'</b></td>';}


	$count = count($this->t['items']);
	$price = new PhocacartPrice();

	foreach($this->t['items'] as $k => $v) {


		$c['title'] .= '<td><h3>'.$v['title'].'</h3>';
		$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $v['image'], 'small');

		if (isset($v['catid2']) && (int)$v['catid2'] > 0 && isset($v['catalias2']) && $v['catalias2'] != '') {
			$link 	= Route::_(PhocacartRoute::getItemRoute($v['id'], $v['catid2'], $v['alias'], $v['catalias2']));
		} else {
			$link 	= Route::_(PhocacartRoute::getItemRoute($v['id'], $v['catid'], $v['alias'], $v['catalias']));
		}


		if (isset($image->rel) && $image->rel != '') {
			$c['title'] .= '<div class="ph-center" >';
			$c['title'] .= '<a href="'.$link.'">';

            $d						= array();
            $d['t']					= $this->t;
            $d['s']			        = $this->s;
            $d['src']				= Uri::base(true).'/'.$image->rel;
            $d['srcset-webp']		= Uri::base(true).'/'.$image->rel_webp;
            $d['alt-value']			= PhocaCartImage::getAltTitle($v['title'], $image->rel);
            $d['class']				= $this->s['c']['img-responsive'];

            $c['title'] .= $layoutI->render($d);


			$c['title'] .= '</a>';
			$c['title'] .= '</div>';
		}
		$c['title'] .= '</td>';

		if ($this->t['can_display_price']) {


			$price 				= new PhocacartPrice;
			$d					= array();
			$d['s']			    = $this->s;
			$d['type']			= $v['type'];// PRODUCTTYPE

			$d['priceitems']	= $price->getPriceItems($v['price'], $v['taxid'], $v['taxrate'], $v['taxcalculationtype'], $v['taxtitle'], $v['unit_amount'], $v['unit_unit'], 1, 1, $v['group_price'], $v['taxhide']);
			$d['priceitemsorig']= array();
			if ($v['price_original'] != '' && $v['price_original'] > 0) {
				$d['priceitemsorig'] = $price->getPriceItems($v['price_original'], $v['taxid'], $v['taxrate'], $v['taxcalculationtype'], '', 0, '', 0, 1, null, $v['taxhide']);
			}
			$d['class']			= 'ph-category-price-box';// we need the same class as category or items view
			$d['product_id']	= (int)$v['id'];
			$d['typeview']		= 'Comparison';

			// Display discount price
			// Move standard prices to new variable (product price -> product discount)
			$d['priceitemsdiscount']		= $d['priceitems'];
			$d['discount'] 					= PhocacartDiscountProduct::getProductDiscountPrice($v['id'], $d['priceitemsdiscount']);

			// Display cart discount (global discount) in product views - under specific conditions only
			// Move product discount prices to new variable (product price -> product discount -> product discount cart)
			$d['priceitemsdiscountcart']	= $d['priceitemsdiscount'];
			$d['discountcart']				= PhocacartDiscountCart::getCartDiscountPriceForProduct($v['id'], $v['catid'], $d['priceitemsdiscountcart']);
			$priceOutput = $layoutP->render($d);

			$c['price'] .= '<td class="ph-right">'.$priceOutput.'</td>';

			$d['zero_price']		= 1;// Apply zero price if possible
		}

		$c['remove'] .= '<td>';
		$c['remove'] .= '<form action="'.$this->t['linkcomparison'].'" method="post">';
		$c['remove'] .= '<input type="hidden" name="id" value="'.(int)$v['id'].'">';
		$c['remove'] .= '<input type="hidden" name="task" value="comparison.remove">';
		$c['remove'] .= '<input type="hidden" name="tmpl" value="component" />';
		$c['remove'] .= '<input type="hidden" name="option" value="com_phocacart" />';
		$c['remove'] .= '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
		$c['remove'] .= '<div class="ph-center">';
		$c['remove'] .= '<button type="submit" class="'.$this->s['c']['btn.btn-danger'].' ph-btn">';
		//$c['remove'] .= ' '<span class="' . $this->s['i']['remove'] . '"></span>';
		$c['remove'] .= PhocacartRenderIcon::icon($this->s['i']['remove']);
		$c['remove'] .= '</button>';
		$c['remove'] .= '</div>';
		$c['remove'] .= HTMLHelper::_('form.token');
		$c['remove'] .= '</form>';
		$c['remove'] .= '</td>';

		$c['desc'] .= '<td>'.HTMLHelper::_('content.prepare', $v['description']).'</td>';
		$c['man'] .= '<td class="ph-center">'.$v['manufacturer_title'].'</td>';

		if ($this->t['value']['stock'] == 1)	{ $c['stock'] 	.= '<td class="ph-center">'.Text::_($v['stock']).'</td>';}

		if ($this->t['value']['length'] == 1)	{ $c['length'] 	.= '<td class="ph-center">'.PhocacartUtils::round($v['length']).' '.$this->t['unit_size'].'</td>';}
		if ($this->t['value']['width'] == 1)	{ $c['width'] 	.= '<td class="ph-center">'.PhocacartUtils::round($v['width']).' '.$this->t['unit_size'].'</td>';}
		if ($this->t['value']['height'] == 1)	{ $c['height'] 	.= '<td class="ph-center">'.PhocacartUtils::round($v['height']).' '.$this->t['unit_size'].'</td>';}
		if ($this->t['value']['weight'] == 1)	{ $c['weight'] 	.= '<td class="ph-center">'.PhocacartUtils::round($v['weight']).' '.$this->t['unit_weight'].'</td>';}
		if ($this->t['value']['volume'] == 1)	{ $c['volume'] 	.= '<td class="ph-center">'.PhocacartUtils::round($v['volume']).' '.$this->t['unit_volume'].'</td>';}


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

		$c2['link'] .= '<td class="ph-center">';
		$c2['link'] .= '<a href="'.$link.'" class="'.$this->s['c']['btn.btn-primary.btn-sm']. ' ph-btn" role="button">'.PhocacartRenderIcon::icon($this->s['i']['search'], '', ' ') .Text::_('COM_PHOCACART_VIEW_PRODUCT').'</a>';
		$c2['link'] .= '</td>';

	}

	$c['title'] 	.= '</tr>';
	if ($this->t['can_display_price']) {
		$c['price'] 	.= '</tr>';
	}
	$c['desc'] 		.= '</tr>';
	$c['man'] 		.= '</tr>';
	$c['remove'] 	.= '</tr>';
	$c2['link']		.= '</tr>';

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
						if (isset($v2[0])) {
							$v2V = $v2[0];
							if (is_array($v2[0])) {
								$v2V = implode ('<br>', $v2[0]);
							}
							echo '<td class="ph-center">'.$v2V.'</td>';
						} else {echo '<td></td>';}
					} else if ($count == 2) {

						if (isset($v2[0])) {
							$v2V = $v2[0];
							if (is_array($v2[0])) {
								$v2V = implode ('<br>', $v2[0]);
							}
							echo '<td class="ph-center">'.$v2V.'</td>';
						} else {echo '<td></td>';}

						if (isset($v2[1])) {
							$v2V = $v2[1];
							if (is_array($v2[1])) {
								$v2V = implode ('<br>', $v2[1]);
							}
							echo '<td class="ph-center">'.$v2V.'</td>';
						} else {echo '<td></td>';}

					} else {
						if (isset($v2[0])) {
							$v2V = $v2[0];
							if (is_array($v2[0])) {
								$v2V = implode ('<br>', $v2[0]);
							}
							echo '<td class="ph-center">'.$v2V.'</td>';
						} else {echo '<td></td>';}

						if (isset($v2[1])) {
							$v2V = $v2[1];
							if (is_array($v2[1])) {
								$v2V = implode ('<br>', $v2[1]);
							}
							echo '<td class="ph-center">'.$v2V.'</td>';
						} else {echo '<td></td>';}

						if (isset($v2[2])) {
							$v2V = $v2[2];
							if (is_array($v2[2])) {
								$v2V = implode ('<br>', $v2[2]);
							}
							echo '<td class="ph-center">'.$v2V.'</td>';
						} else {echo '<td></td>';}
					}

					echo'</tr>';
				}
			}
		}
	}

	// Link to product
	foreach($c2 as $k => $v) {
		echo $v;
	}

	echo '</table>';
	echo '</div>';// end comparison items
} else {
	echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_THERE_ARE_NO_PRODUCTS_IN_COMPARISON_LIST')));
}



echo '</div>';// end comparison box
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>
