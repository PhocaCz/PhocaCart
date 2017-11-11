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
defined( '_JEXEC' ) or die( 'Restricted access' );

class PhocacartRenderAdminviews
{
	public function __construct(){}
	
	public function jsJorderTable($listOrder) {
		return '<script type="text/javascript">' . "\n"
		.'Joomla.orderTable = function() {' . "\n"
		.'  table = document.getElementById("sortTable");' . "\n"
		.'  direction = document.getElementById("directionTable");' . "\n"
		.'  order = table.options[table.selectedIndex].value;' . "\n"
		.'  if (order != \''. $listOrder.'\') {' . "\n"
		.'    dirn = \'asc\';' . "\n"
		.'	} else {' . "\n"
		.'    dirn = direction.options[direction.selectedIndex].value;' . "\n"
		.'  }' . "\n"
		.'  Joomla.tableOrdering(order, dirn, \'\');' . "\n"
		.'}' . "\n"
		.'</script>' . "\n";
	}
	
	public function startForm($option, $view, $id = 'adminForm', $name = 'adminForm') {
		return '<div id="'.$view.'"><form action="'.JRoute::_('index.php?option='.$option.'&view='.$view).'" method="post" name="'.$name.'" id="'.$id.'">'."\n" .  '<div id="phAdminView" class="row-fluid ph-admin-box">';
	}
	
	public function endForm() {
		return '</div></form>'."\n".'</div>'."\n";
	}
	
	public function startFilter($txtFilter = ''){
		//$o = '<div id="j-sidebar-container" class="col-xs-12 col-sm-2 col-md-2">'."\n". JHtmlSidebar::render()."\n";
		$o = '<div class="col-xs-12 col-sm-2 col-md-2 ph-admin-box-menu">'."\n". JHtmlSidebar::render()."\n";
		
		if ($txtFilter != '') {
			$o .= '<hr />'."\n" . '<div class="filter-select ">'."\n"
			. '<h4 class="page-header">'. JText::_($txtFilter).'</h4>'."\n";
		} else {
			$o .= '<div>';
		}
		
		return $o;
	}

	public function endFilter() {
		return '</div>' . "\n" . '</div>' . "\n";
	}
	
	public function selectFilterPublished($txtSp, $state) {
		return '<div class="btn-group pull-right ph-select-status">'. "\n"
		.'<select name="filter_published" class="inputbox" onchange="this.form.submit()">'."\n"
		. '<option value="">'.JText::_($txtSp).'</option>'
		. JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('archived' => 0, 'trash' => 0)), 'value', 'text', $state, true)
		.'</select></div>'. "\n";
	}
	
	public function selectFilterType($txtSp, $type, $typeList) {
		return '<div class="btn-group pull-right">'. "\n"
		.'<select name="filter_type" class="inputbox" onchange="this.form.submit()">'."\n"
		. '<option value="">'.JText::_($txtSp).'</option>'
		. JHtml::_('select.options', $typeList, 'value', 'text', $type, true)
		.'</select></div>'. "\n";
	}
	
	public function selectFilterLanguage($txtLng, $state) {
		return '<div class="btn-group pull-right">'. "\n"
		.'<select name="filter_language" class="inputbox" onchange="this.form.submit()">'."\n"
		. '<option value="">'.JText::_($txtLng).'</option>'
		. JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $state)
		.'</select></div>'. "\n";
	}
	
	public function selectFilterCategory($categoryList, $txtLng, $state) {
		
		$o = '<div class="btn-group pull-right ">'. "\n"
		.'<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">'."\n";
		
		if ($txtLng != '') {
			$o .= '<option value="">'.JText::_($txtLng).'</option>';
		}
		$o .= JHtml::_('select.options', $categoryList, 'value', 'text', $state)
		. '</select></div>'. "\n";
		return $o;
	}
	
	
	
	public function selectFilterLevels($txtLng, $state) {
		$levelList = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
		return 
		'<div class="btn-group pull-right">'. "\n"
		.'<select name="filter_level" class="inputbox" onchange="this.form.submit()">'."\n"
		. '<option value="">'.JText::_($txtLng).'</option>'
		. JHtml::_('select.options', $levelList, 'value', 'text', $state)
		. '</select></div>'. "\n";
	}
	
	public function selectFilterCountry($countryList, $txtLng, $state) {
		return '<div class="btn-group pull-right ">'. "\n"
		.'<select name="filter_country_id" class="inputbox" onchange="this.form.submit()">'."\n"
		. '<option value="">'.JText::_($txtLng).'</option>'
		. JHtml::_('select.options', $countryList, 'value', 'text', $state)
		. '</select></div>'. "\n";
	}
	
	public function startMainContainer() {
		//return '<div id="j-main-container" class="col-xs-12 col-sm-10 col-md-10">'. "\n";
		$o = '<div id="j-main-container" class="col-xs-12 col-sm-10 col-md-10 ph-admin-box-content ph-admin-manage">'. "\n";
		$o .= '<div id="ph-system-message-container"></div>'. "\n";// specific container for moving messages from joomla to phoca
		PhocacartRenderAdminjs::moveSystemMessageFromJoomlaToPhoca();
		return $o;
	}
	
	public function endMainContainer() {
		return '</div>'. "\n";
	}
	
	public function startFilterBar($id = 0) {
		if ((int)$id > 0) {
			return '<div id="filter-bar'.$id.'" class="btn-toolbar ph-btn-toolbar-'.$id.'">'. "\n";
		} else {
			return '<div id="filter-bar'.$id.'" class="btn-toolbar">'. "\n";
		}
		
	}
	
	public function endFilterBar() {
		return '</div>' . "\n" . '<div class="clearfix"> </div>'. "\n";
	}

	public function inputFilterSearch($txtSl, $txtSd, $state) {
		return '<div class="filter-search btn-group pull-left">'. "\n"
		.'<label for="filter_search" class="element-invisible">'.JText::_($txtSl).'</label>'. "\n"
		.'<input type="text" name="filter_search" placeholder="'.JText::_($txtSd).'" id="filter_search"'
		.' value="'.$state.'" title="'.JText::_($txtSd).'" />'. "\n"
		.'</div>'. "\n";
	}
	
	public function inputFilterUser($txtSl, $txtSd, $state, $userName) {
		$o = '<div class="filter-user btn-group pull-left">'. "\n";
		
		$d = array();
		$d['readonly'] = 0;
		$d['required'] = 0;
		$d['userName'] = $userName;
		$d['name'] = 'filter_user';
		$d['value'] = (int)$state;
		$d['id'] = (int)$state;
		$d['class'] = '';
		$d['size'] = '';
		$d['onchange'] = '';

		$layoutU 	= new JLayoutFile('joomla.form.field.user', null);
		$o .= $layoutU->render($d);
		
		
		$o .= '</div>'. "\n";
		
		return $o;
	}
	

	
	public function inputFilterSearchClear($txtFs, $txtFc, $clearClass = array()) {
		
		$clearString = '';
		if (!empty($clearClass)) {
			foreach($clearClass as $k => $v) {
				//$clearString .= 'document.getElementsByName(\''.$v.'\').value=\'\';';
				$clearString .= 'jQuery(\'.'.$v.'\').val(\'\');';
			}
		}

		return '<div class="btn-group pull-left ">'. "\n"
		.'<button class="btn tip hasTooltip" type="submit" title="'.JText::_($txtFs).'"><i class="icon-search"></i></button>'. "\n"
		.'<button class="btn tip hasTooltip" type="button" id="phOnClickClear" onclick="document.getElementById(\'filter_search\').value=\'\'; '.$clearString.'this.form.submit();"'
		.' title="'.JText::_($txtFc).'"><i class="icon-remove"></i></button>'. "\n"
		.'</div>'. "\n";
	}
	
	public function inputFilterSearchLimit($txtSl, $paginationLimitBox) {			
		return '<div class="btn-group pull-right ">'. "\n"
		.'<label for="limit" class="element-invisible">'.JText::_($txtSl).'</label>'. "\n"
		.$paginationLimitBox ."\n" . '</div>'. "\n";
	}
	
	public function selectFilterDirection($txtOd, $txtOasc, $txtOdesc, $listDirn) {
		$ascDir = $descDir = '';
		if ($listDirn == 'asc') {$ascDir = 'selected="selected"';}
		if ($listDirn == 'desc') {$descDir = 'selected="selected"';}
		return '<div class="btn-group pull-right ">'. "\n"
		.'<label for="directionTable" class="element-invisible">' .JText::_('JFIELD_ORDERING_DESC').'</label>'. "\n"
		.'<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">'. "\n"
		.'<option value="">' .JText::_('JFIELD_ORDERING_DESC').'</option>'. "\n"
		.'<option value="asc" '.$ascDir.'>' . JText::_('JGLOBAL_ORDER_ASCENDING').'</option>'. "\n"
		.'<option value="desc" '.$descDir.'>' . JText::_('JGLOBAL_ORDER_DESCENDING').'</option>'. "\n"
		.'</select>'. "\n"
		.'</div>'. "\n";
	}
	
	public function selectFilterSortBy($txtSb, $sortFields, $listOrder) {
		return '<div class="btn-group pull-right">'. "\n"
		.'<label for="sortTable" class="element-invisible">'.JText::_($txtSb).'</label>'. "\n"
		.'<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">'. "\n"
		.'<option value="">'.JText::_($txtSb).'</option>'. "\n"
		. JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder). "\n"
		.'</select>'. "\n"
		.'</div>'. "\n";
	}
	
	
	public function startTable($id) {
		return '<table class="table table-striped" id="'.$id.'">'. "\n";
	}
	
	public function endTable() {
		return '</table>'. "\n";
	}
	public function tblFoot($listFooter, $columns) {
		return '<tfoot>' . "\n" . '<tr><td colspan="'.(int)$columns.'">'.$listFooter.'</td></tr>'. "\n".'</tfoot>'. "\n";
	}
	
	public function startTblHeader() {
		return 	'<thead>'."\n".'<tr>'."\n";
	}
	
	public function endTblHeader() {
		return 	'</tr>'."\n".'</thead>'."\n";
	}
	
	public function thOrdering($txtHo, $listDirn, $listOrder, $prefix = 'a', $empty = false ) {
		
		if ($empty) {
			return '<th class="nowrap center  ph-ordering"></th>'. "\n";
		}
		
		return '<th class="nowrap center  ph-ordering">'. "\n"
		. JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', strip_tags($prefix).'.ordering', $listDirn, $listOrder, null, 'asc', $txtHo). "\n"
		. '</th>';
	}
	
	public function thCheck($txtCh) {
		return '<th class=" ph-check">'. "\n"
		.'<input type="checkbox" name="checkall-toggle" value="" title="'.JText::_($txtCh).'" onclick="Joomla.checkAll(this)" />'. "\n"
		.'</th>'. "\n";
	}
	
	public function tdOrder($canChange, $saveOrder, $orderkey, $catOrderingEnabled = true){
	
		$o = '<td class="order nowrap center ">'. "\n";
		if ($canChange) {
			$disableClassName = '';
			$disabledLabel    = '';
			if (!$saveOrder) {
				$disabledLabel    = JText::_('JORDERINGDISABLED');
				$disableClassName = 'inactive tip-top';
			}
			if (!$catOrderingEnabled) {
				$disabledLabel    = JText::_('COM_PHOCACART_SELECT_CATEGORY_TO_ORDER_PRODUCTS');
			}
			$o .= '<span class="sortable-handler hasTooltip '.$disableClassName.'" title="'.$disabledLabel.'"><i class="icon-menu"></i></span>'."\n";
		} else {
			$o .= '<span class="sortable-handler inactive"><i class="icon-menu"></i></span>'."\n";
		}
		$orderkeyPlus = $orderkey + 1;
		$o .= '<input type="text" style="display:none" name="order[]" size="5" value="'.$orderkeyPlus.'" />'. "\n"
		.'</td>'. "\n"; 
		return $o;
	}
	
	public function tdRating($ratingAvg) {
		$o = '<td class="small ">';
		$voteAvg 		= round(((float)$ratingAvg / 0.5)) * 0.5;
		$voteAvgWidth	= 16 * $voteAvg;
		$o .= '<ul class="star-rating-small">'
		.'<li class="current-rating" style="width:'.$voteAvgWidth.'px"></li>'
		.'<li><span class="star1"></span></li>';

		for ($ir = 2;$ir < 6;$ir++) {
			$o .= '<li><span class="stars'.$ir.'"></span></li>';
		}
		$o .= '</ul>';			
		$o .='</td>'. "\n";
		return $o;
	}
	
	public function tdLanguage($lang, $langTitle, $langTitleE ) {
	
		$o = '<td class="small nowrap ">';
		if ($lang == '*') {
			$o .= JText::_('JALL');
		} else {
			if ($langTitle) {
				$o .= $langTitleE;
			} else {
				$o .= JText::_('JUNDEFINED');;
			}
		}
		$o .= '</td>'. "\n";
		return $o;
	}
	

	
	public function formInputs($listOrder, $listDirn, $originalOrders) {
	
		return '<input type="hidden" name="task" value="" />'. "\n"
		.'<input type="hidden" name="boxchecked" value="0" />'. "\n"
		.'<input type="hidden" name="filter_order" value="'.$listOrder.'" />'. "\n"
		.'<input type="hidden" name="filter_order_Dir" value="'.$listDirn.'" />'. "\n"
		. JHtml::_('form.token'). "\n"
		.'<input type="hidden" name="original_order_values" value="'. implode($originalOrders, ',').'" />'. "\n";
	}
	
	public function td($value, $class = '') {
		if ($class != ''){
			return '<td class="'.$class.'">'. $value.'</td>'. "\n";
		} else {
			return '<td>'. $value.'</td>'. "\n";
		}
	}
	
	/* TO DO:
	* CHANGE PATHS
	* SET NEW PARAM IN PG: '/media/com_phocacart/images/administrator/'
	*/
	public function tdImage($item, $button, $txtE, $class = '', $avatarAbs = '', $avatarRel = '') {
		$o = '<td class="'.$class.'">'. "\n";
		$o .= '<div class="phocagallery-box-file">'. "\n"
			.' <center>'. "\n"
			.'  <div class="phocagallery-box-file-first">'. "\n"
			.'   <div class="phocagallery-box-file-second">'. "\n"
			.'    <div class="phocagallery-box-file-third">'. "\n"
			.'     <center>'. "\n";
			
		if ($avatarAbs != '' && $avatarRel != '') {
			// AVATAR
			if (JFile::exists($avatarAbs.$item->avatar)){
				$o .= '<a class="'. $button->modalname.'"'
				.' title="'. $button->text.'"'
				.' href="'.JURI::root().$avatarRel.$item->avatar.'" '
				.' rel="'. $button->options.'" >'
				.'<img src="'.JURI::root().$avatarRel.$item->avatar.'?imagesid='.md5(uniqid(time())).'" alt="'.JText::_($txtE).'" />'
				.'</a>';
			} else {
				$o .= JHTML::_( 'image', '/media/com_phocagallery/images/administrator/phoca_thumb_s_no_image.gif', '');
			}
		} else {	
			// PICASA
			if (isset($item->extid) && $item->extid !='') {									
				
				$resW				= explode(',', $item->extw);
				$resH				= explode(',', $item->exth);
				$correctImageRes 	= PhocaGalleryImage::correctSizeWithRate($resW[2], $resH[2], 50, 50);
				$imgLink			= $item->extl;
				
				$o .= '<a class="'. $button->modalname.'" title="'.$button->text.'" href="'. $imgLink .'" rel="'. $button->options.'" >'
				. '<img src="'.$item->exts.'?imagesid='.md5(uniqid(time())).'" width="'.$correctImageRes['width'].'" height="'.$correctImageRes['height'].'" alt="'.JText::_($txtE).'" />'
				.'</a>'. "\n";
			} else if (isset ($item->fileoriginalexist) && $item->fileoriginalexist == 1) {
				
				$imageRes			= PhocaGalleryImage::getRealImageSize($item->filename, 'small');
				$correctImageRes 	= PhocaGalleryImage::correctSizeWithRate($imageRes['w'], $imageRes['h'], 50, 50);
				$imgLink			= PhocaGalleryFileThumbnail::getThumbnailName($item->filename, 'large');
				
				$o .= '<a class="'. $button->modalname.'" title="'. $button->text.'" href="'. JURI::root(). $imgLink->rel.'" rel="'. $button->options.'" >'
				. '<img src="'.JURI::root().$item->linkthumbnailpath.'?imagesid='.md5(uniqid(time())).'" width="'.$correctImageRes['width'].'" height="'.$correctImageRes['height'].'" alt="'.JText::_($txtE).'" />'
				.'</a>'. "\n";
			} else {
				$o .= JHTML::_( 'image', 'media/com_phocagallery/images/administrator/phoca_thumb_s_no_image.gif', '');
			}
		}
		$o .= '     </center>'. "\n"
			.'    </div>'. "\n"
			.'   </div>'. "\n"
			.'  </div>'. "\n"
			.' </center>'. "\n"
			.'</div>'. "\n";
		$o .=  '</td>'. "\n";
		return $o;
	}
	
	
	public function tdPublishDownUp ($publishUp, $publishDown, $langPref) {
		
		$o				= '';
		$db				= JFactory::getDBO();
		//$app			= JFactory::getApplication();
		$nullDate 		= $db->getNullDate();
		$now			= JFactory::getDate();
		$config			= JFactory::getConfig();
		$publish_up 	= JFactory::getDate($publishUp);
		$publish_down 	= JFactory::getDate($publishDown);
		$tz 			= new DateTimeZone($config->get('offset'));
		$publish_up->setTimezone($tz);
		$publish_down->setTimezone($tz);
		
		
		if ( $now->toUnix() <= $publish_up->toUnix() ) {
			$text = JText::_( $langPref . '_PENDING' );
		} else if ( ( $now->toUnix() <= $publish_down->toUnix() || $publishDown == $nullDate ) ) {
			$text = JText::_( $langPref . '_ACTIVE' );
		} else if ( $now->toUnix() > $publish_down->toUnix() ) {
			$text = JText::_( $langPref . '_EXPIRED' );
		}

		$times = '';
		if (isset($publishUp)) {
			if ($publishUp == $nullDate) {
				$times .= JText::_( $langPref . '_START') . ': '.JText::_( $langPref . '_ALWAYS' );
			} else {
				$times .= JText::_( $langPref . '_START') .": ". $publish_up->format("D, d M Y H:i:s");
			}
		}
		if (isset($publishDown)) {
			if ($publishDown == $nullDate) {
				$times .= "<br />". JText::_( $langPref . '_FINISH'). ': '. JText::_( $langPref . '_NO_EXPIRY' );
			} else {
				$times .= "<br />". JText::_( $langPref . '_FINISH') .": ". $publish_down->format("D, d M Y H:i:s");
			}
		}

		if ( $times ) {
			$o .= '<td align="center">'
				.'<span class="editlinktip hasTip" title="'. JText::_( $langPref . '_PUBLISH_INFORMATION' ).'::'. $times.'">'
				.'<a href="javascript:void(0);" >'. $text.'</a></span>'
				.'</td>'. "\n";
		} else {
			$o .= '<td></td>'. "\n";
		}
		return $o;
	}
	
	// Phoca Cart
	public function tdImageCart( $filename, $size, $manager, $class = '') {
		
		$thumbnail = PhocacartFileThumbnail::getThumbnailName($filename, $size, $manager);
		
		if ($class != ''){
			$o = '<td class="'.$class.'">';
		} else {
			$o = '<td>';
		}
		if (JFile::exists($thumbnail->abs)) {
			//$o .= JHTML::_( 'image', $thumbnail->rel . '?imagesid='.md5(uniqid(time())), '');
			$o .= '<img src="'.JURI::root().$thumbnail->rel . '?imagesid='.md5(uniqid(time())).'" />';
		} 
		$o .= '</td>';
		return $o;
	}
}
?>