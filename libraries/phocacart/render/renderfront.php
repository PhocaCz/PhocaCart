<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
class PhocaCartRenderFront
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
		$viewLink		= $menu->query['view'];
		
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
	
		if ($viewLink != $viewCurrent && $header != '') {
			$title = $header;
		} else if (empty($title)) {
			$title = htmlspecialchars_decode($app->getCfg('sitename'));
		} else if ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', htmlspecialchars_decode($app->getCfg('sitename')), $title);
			if ($nameInTitle == 1 && isset($name->title) && $name->title != '') {
				$title = $title .' - ' .  $name->title;
			}
		} else if ($app->getCfg('sitename_pagetitles', 0) == 2) {
			if ($nameInTitle == 1 && isset($name->title) && $name->title != '') {
				$title = $title .' - ' .  $name->title;
			}
			$title = JText::sprintf('JPAGETITLE', $title, htmlspecialchars_decode($app->getCfg('sitename')));
		}
		
		$document->setTitle($title);

		
		
		if (isset($item->metadesc) && $item->metadesc != '') {
			$document->setMetadata('menu-meta_description', $item->metadesc );
		} else if (isset($category->metadesc) && $category->metadesc != '') {
			$document->setMetadata('menu-meta_description', $category->metadesc );
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

		if ($app->getCfg('MetaTitle') == '1' && $params->get('menupage_title', '')) {
			$document->setMetaData('title', $params->get('page_title', ''));
		}
		
		// Breadcrumbs TO DO (Add the whole tree)
		if ($type == 'category') {
			
			$path = PhocaCartCategory::getPath(array(), (int)$category->id, (int)$category->parent_id, $category->title, $category->alias);
			$curpath = $pathway->getPathwayNames();
			
			if (!empty($path)) {
				$path = array_reverse($path);
				foreach ($path as $k => $v) {
					$pathway->addItem($v['title'], JRoute::_(PhocaCartRoute::getCategoryRoute($v['id'], $v['alias'])));
					
				}
			}
			/*if (isset($category->parentid)) {
				if ($category->parentid == 0) {
					// $pathway->addItem( JText::_('COM_PHOCACART_CATEGORIES'), JRoute::_(PhocaCartRoute::getCategoriesRoute()));
				} else if ($category->parentid > 0) {
					$curpath = $pathway->getPathwayNames();
					
					$countCurPath = count($curpath)-1;
					
					if(!isset($curpath[$countCurPath]) || ($category->parenttitle != $curpath[$countCurPath])){
						$pathway->addItem($category->parenttitle, JRoute::_(PhocaCartRoute::getCategoryRoute($category->parentid, $category->parentalias)));
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
			
			$path = PhocaCartCategory::getPath(array(), (int)$category->id, (int)$category->parent_id, $category->title, $category->alias);
			$curpath = $pathway->getPathwayNames();
			
			if (!empty($path)) {
				$path = array_reverse($path);
				foreach ($path as $k => $v) {
					$pathway->addItem($v['title'], JRoute::_(PhocaCartRoute::getCategoryRoute($v['id'], $v['alias'])));
					
				}
			}
			
		/*	if (isset($category->id) && isset($category->title) && isset($category->alias)) {
				if ($category->id > 0) {
					$pathway->addItem($category->title, JRoute::_(PhocaCartRoute::getCategoryRoute($category->id, $category->alias)));
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
	
	public static function displayVideo($url, $view = 0, $ywidth = 0, $yheight = 0) {
	
		$o = '';
		if ($url != '' && PhocaCartUtils::isURLAddress($url) ) {
			
			
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
			
			
			
			/*if ($view == 0) {
				// Category View
				$youtubeheight	= $this->params->get( 'youtube_height_cv', 360 );
				$youtubewidth	= $this->params->get( 'youtube_width_cv', 480 );
			} else {
				// Detail View
				$youtubeheight	= $this->params->get( 'youtube_height_dv', 360 );
				$youtubewidth	= $this->params->get( 'youtube_width_dv', 480 );
			}
			
			if ((int)$ywidth > 0) {
				$youtubewidth	= (int)$ywidth;
			}
			if ((int)$yheight > 0) {
				$youtubeheight	= (int)$yheight;
			}*/

			
			$o .= '<div class="ph-video-container">';
			$o .= '<object data="http://www.youtube.com/embed/'.$code.'"></object>';
			/*$o .= '<object data="http://www.youtube.com/v/'.$code.'" type="application/x-shockwave-flash">'
			.'<param name="movie" value="http://www.youtube.com/v/'.$code.'" />'
			.'<param name="allowFullScreen" value="true" />'
			.'<param name="allowscriptaccess" value="always" />'
			.'<embed src="'.$yLink.$code.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" /></object>';*/
			$o .= '</div>';
			
		}
		return $o;
	}
	
	public static function renderHeader($headers = array(), $tag = 'h1') {
		
		$app			= JFactory::getApplication();
		//$menus		= $app->getMenu();
		//$menu 		= $menus->getActive();
		$p 				= $app->getParams();
		$showPageHeading= $p->get('show_page_heading');
		$pageHeading	= $p->get('page_heading');
		
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
			return '<' . strip_tags($tag) . '>' . implode(" - ", $h) . '</' . strip_tags($tag) . '>';
		}
		return false;
	}
}