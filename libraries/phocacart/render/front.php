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
defined('_JEXEC') or die();
class PhocacartRenderFront
{
	public static function renderNewIcon($date, $size = 1) {
		$app	= JFactory::getApplication();
		$params = $app->getParams();
		$new	= $params->get( 'display_new', 0 );
		
		$o = '';
		if ($new == 0) {
			$o .= '';
		} else {
			$dateAdded 	= strtotime($date, time());
			$dateToday 	= time();
			$dateExists = $dateToday - $dateAdded;
			$dateNew	= (int)$new * 24 * 60 * 60;
			if ($dateExists < $dateNew) {
				$o .= '<div class="ph-corner-icon-wrapper ph-corner-icon'.$size.'-wrapper"><div class="ph-corner-icon ph-corner-icon'.$size.' ph-corner-icon-new">'.JText::_('COM_PHOCACART_NEW').'</div></div>';
				
			}
		}
		return $o;
	}
	
	public static function renderHotIcon($sales, $size = 1) {
		$app	= JFactory::getApplication();
		$params = $app->getParams();
		$hot	= $params->get( 'display_hot', 0 );
		
		$o = '';
		if ($hot == 0) {
			$o .= '';
		} else {
			if ($sales > $hot || $sales == $hot) {
				$o .= '<div class="ph-corner-icon-wrapper  ph-corner-icon'.$size.'-wrapper"><div class="ph-corner-icon ph-corner-icon'.$size.' ph-corner-icon-hot">'.JText::_('COM_PHOCACART_HOT').'</div></div>';
			}
		}
		return $o;
	}
	
	public static function renderFeaturedIcon($featured, $size = 1) {
		$app	= JFactory::getApplication();
		$params = $app->getParams();
		$feat	= $params->get( 'display_featured', '' );
		
		$o = '';
		if ($featured == 0) {
			$o .= '';
		} else {
			$o .= '<div class="ph-corner-icon-wrapper  ph-corner-icon'.$size.'-wrapper"><div class="ph-corner-icon ph-corner-icon'.$size.' ph-corner-icon-featured">'.JText::_($feat).'</div></div>';
		}
		return $o;
	}
	
	public static function prepareDocument($document, $params, $category = false, $item = false, $header = '') {
	
		$app			= JFactory::getApplication();
		$menus			= $app->getMenu();
		$menu 			= $menus->getActive();
		$pathway 		= $app->getPathway();
		$title 			= null;
		$metakey 		= $params->get( 'cart_metakey', '' );
		$metadesc 		= $params->get( 'cart_metadesc', '' );
		$nameInTitle 	= 1;// TO DO possible parameter Category or Title name
		
		$viewCurrent	= $app->input->get('view');
		$viewLink		= '';
		if (isset($menu->query['view'])) {
			$viewLink	= $menu->query['view'];
		}
		
		$type = '';
		$name = array();
		if (!empty($item) && isset($item->title)) {
			$name = $item;
			$type = 'item';
		} else if (!empty($category) && isset($category->title)) {
			$name = $category;
			$type = 'category';
		}

		if ($menu) {
			$params->def('page_heading', $params->get('page_title', $menu->title));
		} else {
			$params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}
		
		$title 			= $params->get('page_title', '');
		
		if (isset($name->title) && $name->title != '') {
			/*if ($title != '') {
				$title = $title .' - ' .  $name->title;
			} else {
				$title = $name->title;
			}*/
			
			$title = $name->title;
		}
	
		if ($viewLink != $viewCurrent && $header != '') {
			$title = $header;
		} else if (empty($title)) {
			$title = htmlspecialchars_decode($app->get('sitename'));
		} else if ($app->get('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', htmlspecialchars_decode($app->get('sitename')), $title);
			if ($nameInTitle == 1 && isset($name->title) && $name->title != '') {
				$title = $title .' - ' .  $name->title;
			}
		} else if ($app->get('sitename_pagetitles', 0) == 2) {
			if ($nameInTitle == 1 && isset($name->title) && $name->title != '') {
				$title = $title .' - ' .  $name->title;
			}
			$title = JText::sprintf('JPAGETITLE', $title, htmlspecialchars_decode($app->get('sitename')));
		}
		
		if ($type == 'category' && isset($category->metatitle) && $category->metatitle != '') {
			$title = $category->metatitle;
		}
		if ($type == 'item' && isset($item->metatitle) && $item->metatitle != '') {
			$title = $item->metatitle;
		}
		
		
		$document->setTitle($title);

		
		
		if (isset($item->metadesc) && $item->metadesc != '') {
			$document->setMetadata('description', $item->metadesc );
		} else if (isset($category->metadesc) && $category->metadesc != '') {
			$document->setMetadata('description', $category->metadesc );
		} else if ($metadesc != '') {
			$document->setDescription($metadesc);
		} else if ($params->get('menu-meta_description', '')) {
			$document->setDescription($params->get('menu-meta_description', ''));
		} 

		if (isset($item->metakey) && $item->metakey != '') {
			$document->setMetadata('keywords', $item->metakey );
		} else if (isset($category->metakey) && $category->metakey != '') {
			$document->setMetadata('keywords', $category->metakey );
		} else if ($metakey  != '') {
			$document->setMetadata('keywords', $metakey );
		} else if ($params->get('menu-meta_keywords', '')) {
			$document->setMetadata('keywords', $params->get('menu-meta_keywords', ''));
		}

		if ($app->get('MetaTitle') == '1' && $params->get('menupage_title', '')) {
			$document->setMetaData('title', $params->get('page_title', ''));
		}
		
		
		
		if (isset($category->metadata)) {
			$registry = new JRegistry;
			$registry->loadString($category->metadata);
			$category->metadata = $registry->toArray();
		}
		
		if (isset($item->metadata)) {
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$category->metadata = $registry->toArray();
		}
		
		if ($type == 'category' && isset($category->metadata['robots']) && $category->metadata['robots'] != '') {
			$document->setMetadata('robots', $category->metadata['robots']);
		}
		if ($type == 'item' && isset($item->metadata['robots']) && $item->metadata['robots'] != '') {
			$document->setMetadata('robots', $item->metadata['robots']);
		}
		
		if ($params->get('robots')){
			$document->setMetadata('robots', $params->get('robots'));
		}
		
		// Breadcrumbs TO DO (Add the whole tree)
		if ($type == 'category') {
			
			$path = PhocacartCategory::getPath(array(), (int)$category->id, (int)$category->parent_id, $category->title, $category->alias);
			//$curpath = $pathway->getPathwayNames();
			$pathWayIdA = $pathway->getPathway();
			$pIdA 		= array();
			if (!empty($pathWayIdA)) {
				foreach($pathWayIdA as $k => $v) {
					if (isset($v->link)) {
						$parts = parse_url($v->link);
						parse_str($parts['query'], $query);
						if (isset($query['id'])){
							$pIdA[] = (int)$query['id'];
						}
					}
				}
			}
			
			if (!empty($path)) {
				$path = array_reverse($path);
				foreach ($path as $k => $v) {
					if (!in_array((int)$v['id'], $pIdA)) {
						// Don't duplicate breadcrumbs
						$pathway->addItem($v['title'], JRoute::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias'])));
						//$pathway->addItem($v['title'], PhocacartRoute::getCategoryRoute($v['id'], $v['alias']));
					}
				}
			}
			
			
			/*if (isset($category->parentid)) {
				if ($category->parentid == 0) {
					// $pathway->addItem( JText::_('COM_PHOCACART_CATEGORIES'), JRoute::_(PhocacartRoute::getCategoriesRoute()));
				} else if ($category->parentid > 0) {
					$curpath = $pathway->getPathwayNames();
					
					$countCurPath = count($curpath)-1;
					
					if(!isset($curpath[$countCurPath]) || ($category->parenttitle != $curpath[$countCurPath])){
						$pathway->addItem($category->parenttitle, JRoute::_(PhocacartRoute::getCategoryRoute($category->parentid, $category->parentalias)));
					}
				}
			}*/

			/*if (isset($category->title) && !empty($category->title)) {
				$curpath = $pathway->getPathwayNames();
				if( (!empty($curpath) && $category->title != $curpath[count($curpath)-1]) || empty($curpath)){
					$pathway->addItem($category->title);
				}
			}*/
		} else if ($type == 'item' || $type == 'question') {
			
			$path = PhocacartCategory::getPath(array(), (int)$category->id, (int)$category->parent_id, $category->title, $category->alias);
			
			
			$pathWayIdA = $pathway->getPathway();
			$pIdA 		= array();
			
			if (!empty($pathWayIdA)) {
				foreach($pathWayIdA as $k => $v) {
					if (isset($v->link)) {
						$parts = parse_url($v->link);
						parse_str($parts['query'], $query);
						if (isset($query['id'])){
							$pIdA[] = (int)$query['id'];
						}
					}
				}
			}
			
			if (!empty($path)) { 
				$path = array_reverse($path);
				foreach ($path as $k => $v) {
					if (!in_array((int)$v['id'], $pIdA)) {
						// Don't duplicate breadcrumbs
						$pathway->addItem($v['title'], JRoute::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias'])));
						//$pathway->addItem($v['title'], PhocacartRoute::getCategoryRoute($v['id'], $v['alias']));
					}
				}
			}
			
			/*$curpath = $pathway->getPathwayNames();
			
			if (!empty($path)) {
				$path = array_reverse($path);
				foreach ($path as $k => $v) {
					$pathway->addItem($v['title'], JRoute::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias'])));
					
				}
			}*/
			
		/*	if (isset($category->id) && isset($category->title) && isset($category->alias)) {
				if ($category->id > 0) {
					$pathway->addItem($category->title, JRoute::_(PhocacartRoute::getCategoryRoute($category->id, $category->alias)));
				}
			}*/
		
			if (isset($item->title) && !empty($item->title)) {
				$pathway->addItem($item->title);
			}
		} else {
		
			if ($viewCurrent == $viewLink) {
				// Don't add anything to pathway as we display the title from menu link
				// for example - comparision view has an menu link, use menu link title
			} else {
				// we are e.g. in comparison view but we use a menu link from categories
				// so we need to add comparison header if exists
				if ($header != '') {
					$pathway->addItem($header);
				}
			}	
		}
	}
	
	
	
	public static function renderHeader($headers = array(), $tag = '') {
		
		$app			= JFactory::getApplication();
		//$menus		= $app->getMenu();
		//$menu 		= $menus->getActive();
		$p 				= $app->getParams();
		$showPageHeading= $p->get('show_page_heading');
		$pageHeading	= $p->get('page_heading');
		$displayHeader	= $p->get('display_header', 'h1');
		
		if ($displayHeader == '-1') {
			return '';
		}
		
		if ($tag == '') {
			$tag = $displayHeader;
		}
		
		$h = array();
		if ($showPageHeading && $pageHeading != '') { 
			$h[] = htmlspecialchars($pageHeading); 
		}
		
		if (!empty($headers)) {
			foreach ($headers as $k => $v) {
				if ($v != '') {
					$h[] = htmlspecialchars($v);
					break; // in array there are stored OR items (if empty try next, if not empty use this and do not try next)
					       // PAGE HEADING AND NEXT ITEM OR NEXT NEXT ITEM
				}
			}
		}
		
		if (!empty($h)) {
			return '<' . strip_tags($tag) . ' class="ph-header">' . implode(" - ", $h) . '</' . strip_tags($tag) . '>';
		}
		return false;
	}
	
	/*
	 * if the message type is "order" then display this error message only in case there is no other message
	 */
	
	public static function renderMessageQueue($msg = '', $type = '') {
		$app	= JFactory::getApplication();
		$m 		= $app->getMessageQueue();
		$mO = '';
		
		
		
		if (!empty($m)) {
			$mO .= '<ul id="system-messages">';
			if ($msg != '' && $type != 'order') {
				$mO .=  '<li class=" ">' . $msg . '</li>';  
			}
			
			foreach($m as $k => $v) {
				$mO .=  '<li class="' . $v['type'] . ' ">' . $v['message'] . '</li>';      
		   }
		   $mO .=  '</ul>';
		} else {
			$mO .= '<ul id="system-messages">';
			$mO .= '<li class="ph-msg-error">' . $msg . '</li>';  
			$mO .= '</ul>';
		}
		
		return $mO;
	}
	
	public static function displayLink($title = '', $url = '', $target = "_blank") {
		
		$o = '';
		if ($url != '' && PhocacartUtils::isURLAddress($url) && $title != '') {
			
			$targetO 	= $target != '' ? 'target="'.$target.'"' : '';
			$o 			= '<a href="'.$url.'" '.$targetO.'>' . $title . '</a>';
		
		} else if ($title != '') {
			$o = $title;
		}
		return $o;
	}
	
	public static function displayVideo($url, $view = 0, $ywidth = 0, $yheight = 0) {
	
		$o = '';
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$width		= $paramsC->get( 'video_width', 0 );
		$height		= $paramsC->get( 'video_height', 0 );
				
		if ($url != '' && PhocacartUtils::isURLAddress($url) ) {
			
			
			$ssl 	= strpos($url, 'https');
			$yLink	= 'http://www.youtube.com/v/';
			if ($ssl != false) {
				$yLink = 'https://www.youtube.com/v/';
			}
			
			$shortUrl	= 'http://youtu.be/';
			$shortUrl2	= 'https://youtu.be/';
			$pos 		= strpos($url, $shortUrl);
			$pos2 		= strpos($url, $shortUrl2);
			if ($pos !== false) {
				$code 		= str_replace($shortUrl, '', $url);
			} else if ($pos2 !== false) {
				$code 		= str_replace($shortUrl2, '', $url);
			} else {
				$codeArray 	= explode('=', $url);
				$code 		= str_replace($codeArray[0].'=', '', $url);
			}
			

			
			if ((int)$ywidth > 0) {
				$width	= (int)$ywidth;
			}
			if ((int)$yheight > 0) {
				$height	= (int)$yheight;
			}
			
			$attr = '';
			if ((int)$width > 0) {
				$attr .= ' width="'.(int)$width.'"';
			}
			if ((int)$height > 0) {
				$attr .= ' height="'.(int)$height.'"';
			}
			
			$o .= '<div class="ph-video-container">';
			$o .= '<iframe '.$attr.' src="https://www.youtube.com/embed/'.$code.'"></iframe>';
			$o .= '</div>';
			/*$o .= '<object height="'.(int)$height.'" width="'.(int)$width.'" data="http://www.youtube.com/v/'.$code.'" type="application/x-shockwave-flash">'
			.'<param name="movie" value="http://www.youtube.com/v/'.$code.'" />'
			.'<param name="allowFullScreen" value="true" />'
			.'<param name="allowscriptaccess" value="always" />'
			.'<embed src="'.$yLink.$code.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" height="'.(int)$height.'" width="'.(int)$width.'" /></object>';*/
		}
		return $o;
	}
	
	public static function getLayoutType($layoutType) {
		if ($layoutType == 'list') {
			return 'list';
		} else if ($layoutType == 'gridlist') {
			return 'gridlist';
		} else {
			// Else is important, don't show any other types
			return 'grid';
		}
	}
	
	public static function setActiveLayoutType($layoutType) {
	
		$activeLT[0] = $activeLT[1] = $activeLT[2] = '';
		switch($layoutType) {
			case 'grid': 		$activeLT[0]	= 'active';break;
			case 'gridlist':	$activeLT[1]	= 'active';break;
			case 'list':		$activeLT[2]	= 'active';break;
				
		}
		
		return $activeLT;
	}
	
	public static function getColumnClass($column) {
		return 12/$column;//1,2,3,4,6,12
	}
	
	public static function getLabel($date, $sales, $featured) {
		
		$label 	= array();
		$new 	= $hot = $feat = '';
		$c 		= 1;
		
		$label['new']	= self::renderNewIcon($date, $c);
		if ($label['new'] != '') {$c++;}
		$label['hot']	= self::renderHotIcon($sales, $c);
		if ($label['hot'] != '') { $c++;}
		$label['feat']	= self::renderFeaturedIcon($featured, $c);
		
		$label['cssthumbnail'] = '';
		$label['cssthumbnail2'] = 'img-thumbnail';
		if ($c > 1) {
			$label['cssthumbnail'] = 'b-thumbnail';
			$label['cssthumbnail2'] = '';
		}
		
		return $label;
	}
	
	public static function getLinkedTitle($link, $item, $route = 'item') {
		
		if ($link == 1) {
			if ($route == 'item') {
				return '<a href="'.JRoute::_(PhocacartRoute::getItemRoute($item->id, $item->catid, $item->alias, $item->catalias)).'">'.$item->title.'</a>';
			}
		} else {
			return $item->title;
		}
		return '';
	}
	
	public static function renderProductHeader($link, $v, $route = 'item', $tag = '', $additionalClass = '') {
		
		$app			= JFactory::getApplication();
		$p 				= $app->getParams();
		
		$displayHeader	= $p->get('display_product_header', 'h3');

		
		if ($displayHeader == '-1') {
			return '';
		}
		
		if ($tag == '') {
			$tag = $displayHeader;
		}
		
		
		$header = PhocacartRenderFront::getLinkedTitle($link, $v, $route);
		
		return '<' . strip_tags($tag) . ' class="ph-product-header '.strip_tags($additionalClass).'">' . $header . '</' . strip_tags($tag) . '>';
	}
	
	public static function renderArticle($id, $format = 'html', $default = '') {
		$o = '';
		if ((int)$id > 0) {
			$db		= JFactory::getDBO();
			$query	= 'SELECT a.introtext, a.fulltext FROM #__content AS a WHERE id = '.(int)$id;
			$db->setQuery((string)$query);
			$a = $db->loadObject();
			$o = $a->introtext . $a->fulltext;
			
			// Disable emailclock for PDF | MAIL
			if ($format == 'pdf' || $format == 'mail') {
				$o = '{emailcloak=off}' . $o;
			}
			
			$o = JHtml::_('content.prepare', $o );
			
			if ($format == 'pdf') {
				// Remove Javascript for PDF
				$o = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $o);
				// Remove mailto - problematic for TCPDF
				$o = preg_replace("~<a\s+href=[\'|\"]mailto:(.*?)[\'|\"].*?>.*?</a>~", "$1", $o);
				
			}
			
		}
		
		// If no text is set by article and there is default value
		if ($o == '' && $default != '') {
			return $default;
		}
		return $o;
	}
	
	public static function getConfirmOrderText($orderValue) {
		
		$cFT 						= JText::_('COM_PHOCACART_CONFIRM_ORDER');
		
		$app						= JFactory::getApplication();
		$p 							= $app->getParams();	
		$confirm_order_text			= $p->get('cofirm_order_text', '');
		$confirm_order_text_zero	= $p->get('cofirm_order_text_zero', '');
		
		if ($confirm_order_text != '' && $orderValue > 0) {
			$cFT 						= JText::_($confirm_order_text);
		}
		if ($confirm_order_text_zero != '' && $orderValue == 0) {
			$cFT 						= JText::_($confirm_order_text_zero);
		}
		
		return $cFT;
	}
	
}