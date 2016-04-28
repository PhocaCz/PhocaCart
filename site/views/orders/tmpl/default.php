<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div id="ph-pc-orders-box" class="pc-orders-view'.$this->p->get( 'pageclass_sfx' ).'">';
if ( $this->p->get( 'show_page_heading' ) ) { 
	echo '<h1>'. $this->escape($this->p->get('page_heading')) . '</h1>';
} else {
	echo '<h1>'. JText::_('COM_PHOCACART_ORDERS') . '</h1>';
}
/*if ( $this->t['description'] != '') {
	echo '<div class="ph-desc">'. $this->t['description']. '</div>';
}*/

if ((int)$this->u->id > 0 || $this->t['token'] != '') {

	echo '<div class="ph-orders-header-box-row" >';
	
	echo '<div class="col-sm-2 col-md-2 ">'.JText::_('COM_PHOCACART_ORDER_NUMBER').'</div>';
	echo '<div class="col-sm-2 col-md-2 ">'.JText::_('COM_PHOCACART_STATUS').'</div>';
	echo '<div class="col-sm-3 col-md-3 ">'.JText::_('COM_PHOCACART_DATE_ADDED').'</div>';
	echo '<div class="col-sm-2 col-md-2 ph-right">'.JText::_('COM_PHOCACART_TOTAL').'</div>';
	echo '<div class="col-sm-3 col-md-3 ph-center">'.JText::_('COM_PHOCACART_ACTION').'</div>';
	
	echo '<div class="ph-cb"></div>';
	echo '</div>';
	if (!empty($this->t['orders'])) {
		
		$price			= new PhocaCartPrice();
		foreach($this->t['orders'] as $k => $v) {
			echo '<div class="col-sm-12 col-md-12 ph-orders-item-box-row" >';

			echo '<div class="col-sm-2 col-md-2 ">'.PhocaCartOrder::getOrderNumber($v->id).'</div>';
			$status = '<span class="label label-default">'.JText::_($v->status_title).'</span>';
			echo '<div class="col-sm-2 col-md-2 ">'.$status.'</div>';
			
			echo '<div class="col-sm-3 col-md-3 ">'.JHtml::date($v->date, 'd. m. Y. h:s').'</div>';
			
			$price->setCurrency($v->currency_id);
			$total = $price->getPriceFormat($v->total_amount);
			echo '<div class="col-sm-2 col-md-2 ph-right">'.$total.'</div>';
			
			echo '<div class="col-sm-3 col-md-3 ph-center">';
			
			$token = '';
			if ($this->t['token'] != '') {
				$token = '&o='.$this->t['token'];
			}
			$linkOrderView 		= JRoute::_( 'index.php?option=com_phocacart&view=order&tmpl=component&id='.(int)$v->id.'&type=1'.$token );
			$linkInvoiceView 	= JRoute::_( 'index.php?option=com_phocacart&view=order&tmpl=component&id='.(int)$v->id.'&type=2'.$token );
			$linkDelNoteView 	= JRoute::_( 'index.php?option=com_phocacart&view=order&tmpl=component&id='.(int)$v->id.'&type=3'.$token );

			$linkOrderViewHandler= 'onclick="window.open(this.href, \'orderview\', \'width=780,height=560,scrollbars=yes,menubar=no,resizable=yes\');return false;"';
			
			$view = '<a href="'.$linkOrderView.'" class="btn btn-success btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_ORDER').'" class="glyphicon glyphicon-search icon-search"></span></a>';
			$view .= ' <a href="'.$linkInvoiceView.'" class="btn btn-danger btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_INVOICE').'" class="glyphicon glyphicon-list-alt icon-ph-invoice"></span></a>';
			$view .= ' <a href="'.$linkDelNoteView.'" class="btn btn-warning btn-small btn-xs ph-btn" role="button" '.$linkOrderViewHandler.'><span title="'.JText::_('COM_PHOCACART_VIEW_DELIVERY_NOTE').'" class="glyphicon glyphicon-barcode icon-ph-del-note"></span></a>';

			
			echo $view;
			
			echo '</div>';
			
			
			echo '<div class="ph-cb"></div>';
			echo '</div>';
		
		}
	} else {
		echo '<div class="alert alert-error alert-danger">'. JText::_('COM_PHOCACART_NO_ORDERS_ACCOUNT'). '</div>';
	}

} else {
	echo '<div class="alert alert-error alert-danger">'. JText::_('COM_PHOCACART_NOT_LOGGED_IN_PLEASE_LOGIN'). '</div>';
}

/*
if (!empty($this->t['categories'])) {
	echo '<div class="ph-categories">';
	$i = 0;
	$c = count($this->t['categories']);
	$nc= (int)$this->t['columns_cats'];
	$nw= 12/$nc;//1,2,3,4,6,12
	echo '<div class="row">';
	foreach ($this->t['categories'] as $v) {
		
		//if ($i%$nc==0) { echo '<div class="row">';}
		
		echo '<div class="col-sm-6 col-md-'.$nw.'">';
		echo '<div class="thumbnail ph-thumbnail">';
		
		$image 	= PhocaCartImage::getThumbnailName($this->t['path'], $v->image, 'medium');
		$link	= JRoute::_(PhocaCartRoute::getCategoryRoute($v->id, $v->alias));
		
		if (isset($image->rel) && $image->rel != '') {
			echo '<a href="'.$link.'">';
			echo '<img class="img-responsive ph-image" src="'.JURI::base(true).'/'.$image->rel.'" alt=""';
			if (isset($this->t['image_width_cats']) && $this->t['image_width_cats'] != '' && isset($this->t['image_height_cats']) && $this->t['image_height_cats'] != '') {
				echo ' style="width:'.$this->t['image_width_cats'].';height:'.$this->t['image_height_cats'].'"';
			}
			echo ' />';
			echo '</a>';
		}
		echo '<div class="caption">';
		echo '<h3>'.$v->title.'</h3>';
	
		if (!empty($v->subcategories) && (int)$this->t['display_subcat_cats_view'] > 0) {
			echo '<ul>';
			$j = 0;
			foreach($v->subcategories as $v2) {
				if ($j == (int)$this->t['display_subcat_cats_view']) {
					break;
				}
				echo '<li><a href="'.$link.'">'.$v2->title.'</a></li>';
				$j++;
			}
			echo '</ul>';
		}
		
		// Description box will be displayed even no description is set - to set height and have all columns same height
		echo '<div class="ph-cat-desc">';
		if ($v->description != '') {
			echo $v->description;
		}
		echo '</div>';
		
		echo '<p class="pull-right"><a href="'.JRoute::_(PhocaCartRoute::getCategoryRoute($v->id, $v->alias)).'" class="btn btn-primary" role="button">'.JText::_('COM_PHOCACART_VIEW_CATEGORY').'</a></p>';
		echo '<div class="clearfix"></div>';
		echo '</div>';
		echo '</div>';
		echo '</div>'. "\n";
		
		$i++;
		// if ($i%$nc==0 || $c==$i) { echo '</div>';}
	}
	echo '</div></div>'. "\n";
}*/
echo '</div>';
echo '<div>&nbsp;</div>';
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>