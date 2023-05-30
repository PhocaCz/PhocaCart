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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class PhocacartSection
{

	public static function renderTitleAndBackButton($sectionId, $unitId) {


	    $s              = PhocacartRenderStyle::getStyles();
		$section		= PhocacartSection::getSectionById($sectionId);
		$unit			= PhocacartUnit::getUnitById($unitId);

		$o = '<div class="ph-link-sections">';
		if (isset($section->title) && $section->title != '' && isset($unit->title) && $unit->title != '') {

			// Section including unit
			$unitSectionTitle	= $section->title . ' ('.$unit->title.')';
			$linkSection 		= Route::_(PhocacartRoute::getPosRoute(1, 0, 0, 'section', $sectionId));

			$o .= '<div class="'.$s['c']['btn-group'].'" role="group">';
			$o .= '<a href="'.$linkSection.'" class="'.$s['c']['btn.btn-primary'].' active">';
	       // $o .= '<span class="'.$s['i']['back-category'].' icon-white" aria-hidden="true"></span>';
            $o .= PhocacartRenderIcon::icon($s['i']['back-category'].' icon-white', 'aria-hidden="true"');
            $o .= '</a>';
	        $o .= '<a href="'.$linkSection.'" class="'.$s['c']['btn.btn-primary'].' active">'.$unitSectionTitle.'</a>';
	        $o .= '</div>';
		} else if (isset($section->title) && $section->title != '') {

			// One section without unit
			$unitSectionTitle	= $section->title;
			$linkSection 		= Route::_(PhocacartRoute::getPosRoute(1, 0, 0, 'section', $sectionId));

			$o .= '<div class="'.$s['c']['btn-group'].'" role="group">';
			$o .= '<a href="'.$linkSection.'" class="'.$s['c']['btn.btn-primary'].' active">';
	        //$o .= '<span class="'.$s['i']['back-category'].' icon-white" aria-hidden="true"></span>';
            $o .= PhocacartRenderIcon::icon($s['i']['back-category'].' icon-white', 'aria-hidden="true"');
            $o .= '</a>';
	        $o .= '<a href="'.$linkSection.'" class="'.$s['c']['btn.btn-primary'].' active">'.$unitSectionTitle.'</a>';
	        $o .= '</div>';
		} else {
			$sections 		= PhocacartSection::getSections();

			$linkSections 	= Route::_(PhocacartRoute::getPosRoute(1, 0, 0, 'section'));
			if (!empty($sections)) {
				foreach($sections as $k => $v) {
					$linkSection 		= Route::_(PhocacartRoute::getPosRoute(1, 0, 0, 'section', (int)$v->id));
					$o .= '<div class="'.$s['c']['btn-group'].'" role="group">';
					$o .= '<a href="'.$linkSection.'" class="'.$s['c']['btn.btn-primary'].' active">';
					//$o .= '<span class="'.$s['i']['back-category'].' icon-white" aria-hidden="true"></span>';
                    $o .= PhocacartRenderIcon::icon($s['i']['back-category'].' icon-white', 'aria-hidden="true"');
                    $o .= '</a>';
					$o .= '<a href="'.$linkSection.'" class="'.$s['c']['btn.btn-primary'].' active">'.Text::_('COM_PHOCACART_SECTIONS').'</a>';
					$o .= '</div>';
					break;
				}
			} else {
				$o .= '<div>&nbsp;</div>';
			}
		}

		$o .= '</div>';


		return $o;

	}

	public static function renderNavigation($sectionId) {

		// $ticketId is active ticket
		$sections = self::getSections();
		$s              = PhocacartRenderStyle::getStyles();

		$o = '<ul class="'.$s['c']['tabnav'].'">';
		if (!empty($sections)) {
			foreach($sections as $k => $v) {

				$active = '';
				if ((int)$v->id == (int)$sectionId) {
					$active = 'active';
				}


				$link = Route::_(PhocacartRoute::getPosRoute(1,0,0, 'section', (int)$v->id));
				$o .= '<li class="'.$s['c']['nav-item'].' '.$active.'">';
				$o .= '<a class="'.$s['c']['nav-link'].' '.$active.'" href="'.$link.'"> '.$v->title.' </a>';
				$o .= '</li>';

			}

		} else {
			$link = Route::_(PhocacartRoute::getPosRoute(1, 0, 0, 'section'));
			$o .= '<li class="'.$s['c']['nav-item'].' active">';
			$o .= '<a class="'.$s['c']['nav-link'].' active" href="'.$link.'">'.Text::_('COM_PHOCACART_DEFAULT_SECTION').'</a>';
			$o .= '</li>';
		}

		$o .= '</ul>';

		return $o;

	}

	public static function getSections($limit = 0) {

		$db 	= Factory::getDBO();
		$query = ' SELECT a.id, a.title FROM #__phocacart_sections AS a'
				.' WHERE a.published = 1'
				.' ORDER BY a.ordering';
				if ((int)$limit > 0) {
					$query .= ' LIMIT '.(int)$limit;
				}
		$db->setQuery($query);
		$sections = $db->loadObjectList();

		return $sections;
	}

	public static function existsSection($sectionId) {

		$db 	= Factory::getDBO();
		$query = ' SELECT id FROM #__phocacart_sections'
				.' WHERE id = '.(int)$sectionId
				.' AND published = 1';
		$db->setQuery($query);
		$result = $db->loadResult();
		if (isset($result) && (int)$result > 0) {
			return $result;
		}
		return false;
	}

	public static function getSectionById($sectionId) {

		$db 	= Factory::getDBO();
		$query = ' SELECT id, title FROM #__phocacart_sections'
				.' WHERE id = '.(int)$sectionId
				.' AND published = 1';
		$db->setQuery($query);
		$result = $db->loadObject();
		return $result;
	}

	public static function options() {

		$db = Factory::getDBO();
		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_sections AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$items = $db->loadObjectList();

		return $items;
	}

}
