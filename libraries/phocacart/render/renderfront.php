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
	public static function renderNewIcon($date) {
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
				$o .= '<div class="ph-new-icon"><span class="label label-warning">'.JText::_('COM_PHOCACART_NEW').'</span></div>';
			}
		}
		return $o;
	}
	
	public static function prepareDocument($document, $params, $category = false, $item = false) {
	
		$app			= JFactory::getApplication();
		$menus			= $app->getMenu();
		$menu 			= $menus->getActive();
		$pathway 		= $app->getPathway();
		$title 			= null;
		$metakey 		= $params->get( 'cart_metakey', '' );
		$metadesc 		= $params->get( 'cart_metadesc', '' );
		$nameInTitle 	= 1;// TODO possible parameter Category or Title name
		
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
		if (empty($title)) {
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
		
		// Breadcrumbs TODO (Add the whole tree)
		if ($type == 'category') {
			if (isset($category->parentid)) {
				if ($category->parentid == 0) {
					// $pathway->addItem( JText::_('COM_PHOCACART_CATEGORIES'), JRoute::_(PhocaCartRoute::getCategoriesRoute()));
				} else if ($category->parentid > 0) {
					$curpath = $pathway->getPathwayNames();
					
					$countCurPath = count($curpath)-1;
					
					if(!isset($curpath[$countCurPath]) || ($category->parenttitle != $curpath[$countCurPath])){
						$pathway->addItem($category->parenttitle, JRoute::_(PhocaCartRoute::getCategoryRoute($category->parentid, $category->parentalias)));
					}
				}
			}

			if (isset($category->title) && !empty($category->title)) {
				$curpath = $pathway->getPathwayNames();
				if( (!empty($curpath) && $category->title != $curpath[count($curpath)-1]) || empty($curpath)){
					$pathway->addItem($category->title);
				}
			}
		} else if ($type == 'item') {
			if (isset($category->id)) {
				if ($category->id > 0) {
					$pathway->addItem($category->title, JRoute::_(PhocaCartRoute::getCategoryRoute($category->id, $category->alias)));
				}
			}
		
			if (isset($item->title) && !empty($item->title)) {
				$pathway->addItem($item->title);
			}
		}
	}
}