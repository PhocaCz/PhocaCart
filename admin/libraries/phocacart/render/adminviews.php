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
defined('_JEXEC') or die('Restricted access');

use Phoca\Render\Adminviews;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class PhocacartRenderAdminviews extends Adminviews
{
    public $view        = '';
    public $viewtype    = 1;
    public $option      = '';
    public $optionLang  = '';
    public $tmpl        = '';
    public $compatible  = false;
    public $sidebar     = true;

    public function __construct() {

        switch($this->view) {

			case 'phocacartcart':
                $this->viewtype = 2;// Edit
			break;

			default:
				$this->viewtype = 1;// Lists
			break;
		}


        parent::__construct();
    }

    public function startMainContainer($id = 'phAdminView', $class = 'ph-admin-box') {

        $idO = '';
        if ($id != '') {
            $idO = ' id="' . $id . '"';
        }

        $classO = ' class="row"';
        if ($class != '') {
            $classO = ' class="row ' . $class . '"';
        }

        $o = array();
        if ($this->compatible) {

            if ($this->sidebar) {

            } else {
                $o[] = '<div' . $idO . $classO . '>';
                $o[] = '<div id="j-sidebar-container" class="col-md-2">' . JHtmlSidebar::render() . '</div>';
                $o[] = '<div id="j-main-container" class="col-md-10">';
            }

        } else {
            $o[] = '<div' . $idO . $classO . '>';
            //$o[] = '<div id="j-sidebar-container" class="span2">' . JHtmlSidebar::render() . '</div>'."\n";

            $o[] = '<div class="col-xs-12 col-sm-2 col-md-2 ph-admin-box-menu">' . JHtmlSidebar::render() . '</div>';

            //$o[] = '<div id="j-main-container" class="span10">'."\n";
            $o[] = '<div id="j-main-container" class="col-xs-12 col-sm-10 col-md-10 ph-admin-box-content ph-admin-manage">' . "\n";
            $o[] = '<div id="ph-system-message-container"></div>' . "\n";// specific container for moving messages from joomla to phoca
            $this->moveSystemMessageFromJoomlaToPhoca();
        }

        return implode("\n", $o);
    }

    public function endMainContainer() {
        $o = array();

        $o[] = '</div>';
        $o[] = '</div>';

        return implode("\n", $o);
    }

    public function startMainContainerNoSubmenu() {
        //return '<div id="j-main-container" class="col-xs-12 col-sm-10 col-md-10">'. "\n";
        $o = '<div id="j-main-container" class="col-xs-12 col-sm-12 col-md-12 ph-admin-box-content ph-admin-manage">' . "\n";
        $o .= '<div id="ph-system-message-container"></div>' . "\n";// specific container for moving messages from joomla to phoca
        $this->moveSystemMessageFromJoomlaToPhoca();
        return $o;
    }




    /*
    public function selectFilterPublished($txtSp, $state) {
        return '<div class="btn-group ph-pull-right ph-select-status">' . "\n"
            . '<select name="filter_published" class="inputbox" onchange="this.form.submit()">' . "\n"
            . '<option value="">' . JText::_($txtSp) . '</option>'
            . HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions', array('archived' => 0, 'trash' => 0)), 'value', 'text', $state, true)
            . '</select></div>' . "\n";
    }

    public function selectFilterType($txtSp, $type, $typeList) {
        return '<div class="btn-group ph-pull-right">' . "\n"
            . '<select name="filter_type" class="inputbox" onchange="this.form.submit()">' . "\n"
            . '<option value="">' . JText::_($txtSp) . '</option>'
            . HTMLHelper::_('select.options', $typeList, 'value', 'text', $type, true)
            . '</select></div>' . "\n";
    }

    public function selectFilterLanguage($txtLng, $state) {
        return '<div class="btn-group ph-pull-right">' . "\n"
            . '<select name="filter_language" class="inputbox" onchange="this.form.submit()">' . "\n"
            . '<option value="">' . JText::_($txtLng) . '</option>'
            . HTMLHelper::_('select.options', HTMLHelper::_('contentlanguage.existing', true, true), 'value', 'text', $state)
            . '</select></div>' . "\n";
    }

    public function selectFilterCategory($categoryList, $txtLng, $state) {

        $o = '<div class="btn-group ph-pull-right ">' . "\n"
            . '<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">' . "\n";

        if ($txtLng != '') {
            $o .= '<option value="">' . JText::_($txtLng) . '</option>';
        }
        $o .= HTMLHelper::_('select.options', $categoryList, 'value', 'text', $state)
            . '</select></div>' . "\n";
        return $o;
    }

    public function selectFilterParameter($parameterList, $txtLng, $state) {

        $o = '<div class="btn-group ph-pull-right ">' . "\n"
            . '<select name="filter_parameter_id" class="inputbox" onchange="this.form.submit()">' . "\n";

        if ($txtLng != '') {
            $o .= '<option value="">' . JText::_($txtLng) . '</option>';
        }
        $o .= HTMLHelper::_('select.options', $parameterList, 'value', 'text', $state)
            . '</select></div>' . "\n";
        return $o;
    }

    public function selectFilterLevels($txtLng, $state) {
        $levelList = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
        return
            '<div class="btn-group ph-pull-right">' . "\n"
            . '<select name="filter_level" class="inputbox" onchange="this.form.submit()">' . "\n"
            . '<option value="">' . JText::_($txtLng) . '</option>'
            . HTMLHelper::_('select.options', $levelList, 'value', 'text', $state)
            . '</select></div>' . "\n";
    }

    public function selectFilterCountry($countryList, $txtLng, $state) {
        return '<div class="btn-group ph-pull-right ">' . "\n"
            . '<select name="filter_country_id" class="inputbox" onchange="this.form.submit()">' . "\n"
            . '<option value="">' . JText::_($txtLng) . '</option>'
            . HTMLHelper::_('select.options', $countryList, 'value', 'text', $state)
            . '</select></div>' . "\n";
    }

    public function selectFilterSection($sectionList, $txtLng, $state) {

        return '<div class="btn-group ph-pull-right ">' . "\n"
            . '<select name="filter_section_id" class="inputbox" onchange="this.form.submit()">' . "\n"
            . '<option value="">' . JText::_($txtLng) . '</option>'
            . HTMLHelper::_('select.options', $sectionList, 'value', 'text', $state)
            . '</select></div>' . "\n";
    }

    public function startFilterBar($id = 0) {
        if ((int)$id > 0) {
            return '<div id="filter-bar' . $id . '" class="btn-toolbar ph-btn-toolbar-' . $id . '">' . "\n";
        } else {
            return '<div id="filter-bar' . $id . '" class="btn-toolbar">' . "\n";
        }

    }

    public function endFilterBar() {
        return '</div>' . "\n" . '<div class="clearfix"> </div>' . "\n";
    }

    public function inputFilterSearch($txtSl, $txtSd, $state) {
        return '<div class="filter-search btn-group ph-pull-left">' . "\n"
            . '<label for="filter_search" class="element-invisible">' . JText::_($txtSl) . '</label>' . "\n"
            . '<input type="text" name="filter_search" placeholder="' . JText::_($txtSd) . '" id="filter_search"'
            . ' value="' . $state . '" title="' . JText::_($txtSd) . '" />' . "\n"
            . '</div>' . "\n";
    }

    public function inputFilterUser($txtSl, $txtSd, $state, $userName) {
        $o = '<div class="filter-user btn-group ph-pull-left">' . "\n";

        $d             = array();
        $d['readonly'] = 0;
        $d['required'] = 0;
        $d['userName'] = $userName;
        $d['name']     = 'filter_user';
        $d['value']    = (int)$state;
        $d['id']       = (int)$state;
        $d['class']    = '';
        $d['size']     = '';
        $d['onchange'] = '';

        $layoutU = new JLayoutFile('joomla.form.field.user', null);
        $o       .= $layoutU->render($d);


        $o .= '</div>' . "\n";

        return $o;
    }

    public function inputFilterSearchClear($txtFs, $txtFc, $clearClass = array()) {

        $clearString = '';
        if (!empty($clearClass)) {
            foreach ($clearClass as $k => $v) {
                //$clearString .= 'document.getElementsByName(\''.$v.'\').value=\'\';';
                $clearString .= 'jQuery(\'.' . $v . '\').val(\'\');';
            }
        }

        return '<div class="btn-group ph-pull-left ">' . "\n"
            . '<button class="btn tip hasTooltip" type="submit" title="' . JText::_($txtFs) . '"><i class="icon-search"></i></button>' . "\n"
            . '<button class="btn tip hasTooltip" type="button" id="phOnClickClear" onclick="document.getElementById(\'filter_search\').value=\'\'; ' . $clearString . 'this.form.submit();"'
            . ' title="' . JText::_($txtFc) . '"><i class="icon-remove"></i></button>' . "\n"
            . '</div>' . "\n";
    }

    public function inputFilterSearchLimit($txtSl, $paginationLimitBox) {


        return '<div class="btn-group ph-pull-right ">' . "\n"
            . '<label for="limit" class="element-invisible">' . JText::_($txtSl) . '</label>' . "\n"
            . $paginationLimitBox . "\n" . '</div>' . "\n";
    }

    public function selectFilterDirection($txtOd, $txtOasc, $txtOdesc, $listDirn) {
        $ascDir = $descDir = '';
        if ($listDirn == 'asc') {
            $ascDir = 'selected="selected"';
        }
        if ($listDirn == 'desc') {
            $descDir = 'selected="selected"';
        }
        return '<div class="btn-group ph-pull-right ">' . "\n"
            . '<label for="directionTable" class="element-invisible">' . JText::_('JFIELD_ORDERING_DESC') . '</label>' . "\n"
            . '<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">' . "\n"
            . '<option value="">' . JText::_('JFIELD_ORDERING_DESC') . '</option>' . "\n"
            . '<option value="asc" ' . $ascDir . '>' . JText::_('JGLOBAL_ORDER_ASCENDING') . '</option>' . "\n"
            . '<option value="desc" ' . $descDir . '>' . JText::_('JGLOBAL_ORDER_DESCENDING') . '</option>' . "\n"
            . '</select>' . "\n"
            . '</div>' . "\n";
    }

    public function selectFilterSortBy($txtSb, $sortFields, $listOrder) {
        return '<div class="btn-group ph-pull-right">' . "\n"
            . '<label for="sortTable" class="element-invisible">' . JText::_($txtSb) . '</label>' . "\n"
            . '<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">' . "\n"
            . '<option value="">' . JText::_($txtSb) . '</option>' . "\n"
            . HTMLHelper::_('select.options', $sortFields, 'value', 'text', $listOrder) . "\n"
            . '</select>' . "\n"
            . '</div>' . "\n";
    }

    public function thOrdering($txtHo, $listDirn, $listOrder, $prefix = 'a', $empty = false) {

        if ($empty) {
            return '<th class="nowrap center  ph-ordering"></th>' . "\n";
        }

        return '<th class="nowrap center  ph-ordering">' . "\n"
            . HTMLHelper::_('searchtools.sort', '<i class="icon-menu-2"></i>', strip_tags($prefix) . '.ordering', $listDirn, $listOrder, null, 'asc', $txtHo) . "\n"
            . '</th>';
    }

     public function formInputs($listOrder, $listDirn, $originalOrders) {

        return '<input type="hidden" name="task" value="" />' . "\n"
            . '<input type="hidden" name="boxchecked" value="0" />' . "\n"
            . '<input type="hidden" name="filter_order" value="' . $listOrder . '" />' . "\n"
            . '<input type="hidden" name="filter_order_Dir" value="' . $listDirn . '" />' . "\n"
            . HTMLHelper::_('form.token') . "\n"
            . '<input type="hidden" name="original_order_values" value="' . implode(',', $originalOrders) . '" />' . "\n";
    }

    // Phoca Cart

    public function tdImageCart($filename, $size, $manager, $class = '') {

        $thumbnail = PhocacartFileThumbnail::getThumbnailName($filename, $size, $manager);

        if ($class != '') {
            $o = '<td class="' . $class . '">';
        } else {
            $o = '<td>';
        }
        if (JFile::exists($thumbnail->abs)) {
            //$o .= HTMLHelper::_( 'image', $thumbnail->rel . '?imagesid='.md5(uniqid(time())), '');
            $o .= '<img src="' . JURI::root() . $thumbnail->rel . '?imagesid=' . md5(uniqid(time())) . '" />';
        }
        $o .= '</td>';
        return $o;
    }

    public function headerItems($items, &$options) {

		$o = array();

		if (!empty($items)) {
			foreach ($items as $k => $v) {

				if (!isset($v['if']) || (isset($v['if']) && $v['if'])) {

					$class	= PhocacartText::filterValue($v['class'], 'alphanumeric2');
					$tool 	= isset($v['tool']) && $v['tool'] != '' ? PhocacartText::filterValue($v['tool'], 'alphanumeric5') : '';
					$title 	= Text::_($v['title']);
					$column = isset($v['column']) && $v['column'] != '' ? PhocacartText::filterValue($v['column'], 'alphanumeric5') : '';

					if ($tool != '') {
						$o[] = '<th class="'.$class.'">' . HTMLHelper::_($tool, $title, $column, $options['listdirn'], $options['listorder']) . '</th>';
					} else {
						$o[] = '<th class="'.$class.'">' . Text::_($title) . '</th>';
					}

					$options['count']++;

				}
			}
		}

		return implode("\n", $o);

	}
        */
}
?>
