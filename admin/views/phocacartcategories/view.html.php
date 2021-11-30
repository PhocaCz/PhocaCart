<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Object\CMSObject;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartCategories extends HtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
    public $filterForm;
    public $activeFilters;

	function display($tpl = null) {

		$this->t			= PhocacartUtils::setVars('category');
		$this->r 			= new PhocacartRenderAdminviews();
		$this->s            = PhocacartRenderStyle::getStyles();
		$model 				= $this->getModel();
		$this->items			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->filterForm   	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');


		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {

			$this->ordering[$item->parent_id][] = $item->id;
		}

		// if search, don't do a tree, only display the searched items
		$this->t['search'] = $this->state->get('filter.search');
		// We need to load all items because of creating tree
		// After creating tree we get info from pagination
		// and will set displaying of categories for current pagination
		//E.g. pagination is limitstart 5, limit 5 - so only categories from 5 to 10 will be displayed

		// the same for max levels
		$this->t['level'] = $this->state->get('filter.level');

		if (!empty($this->items) && !$this->t['search']) {
			$text = ''; // text is tree name e.g. Category >> Subcategory
			$tree = array();
			// Filter max levels
			if (isset($this->t['level']) && $this->t['level'] > 0) {
				$maxLevel = (int)$this->t['level'] + 1;
			} else {
				$maxLevel = false;
			}

			$this->items = $this->processTree($this->items, $tree, 0, $text, -1, 0, '', $maxLevel);

			// Re count the pagination
			$countTotal 		= count($this->items);
			$model->setTotal($countTotal);
			$this->pagination	= $model->getPagination();
		}

		$media = new PhocacartRenderAdminmedia();

        // ASSOCIATION
        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
            //$this->sidebar = JHtmlSidebar::render();
        } else {
            // In article associations modal we need to remove language filter if forcing a language.
            // We also need to change the category filter to show show categories with All or the forced language.
            if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
            {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
                //$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                //$this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);

                // One last changes needed is to change the category filter to just show categories with All language or with the forced language.
                // $this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
            }
        }


		parent::display($tpl);
	}

	protected function addToolbar() {
		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';

		$pC = PhocacartUtils::getComponentParameters();
		$printed_catalog_enable 	= $pC->get( 'printed_catalog_enable', 0);

		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.category_id'));
		ToolbarHelper::title( Text::_( $this->t['l'].'_CATEGORIES' ), 'folder-open' );
		$user  = Factory::getUser();
		$bar = Toolbar::getInstance('toolbar');

		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {

			ToolbarHelper::divider();
			ToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			ToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($canDo->get('core.delete')) {
			ToolbarHelper::deleteList( Text::_( $this->t['l'].'_WARNING_DELETE_ITEMS' ), $this->t['tasks'].'.delete', $this->t['l'].'_DELETE');
		}

		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			HTMLHelper::_('bootstrap.renderModal', 'collapseModal');
			$title = Text::_('JTOOLBAR_BATCH');
			$dhtml = "<joomla-toolbar-button><button data-bs-toggle=\"modal\" data-bs-target=\"#collapseModal\" class=\"btn btn-small\">
						<span class=\"icon-checkbox-partial\" title=\"$title\"></span>
						$title</button></joomla-toolbar-button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		$dhtml = '<joomla-toolbar-button><button class="btn btn-small" onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_MAKE_SELECTION').'\');}else{if(confirm(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_THUMBNAILS_CATEGORIES').'\')){Joomla.submitbutton(\'phocacartcategory.recreate\');}}" ><i class="icon-image" title="'.Text::_('COM_PHOCACART_RECREATE_THUMBS').'"></i> '.Text::_('COM_PHOCACART_RECREATE_THUMBS').'</button></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml);

		$dhtml = '<joomla-toolbar-button><button onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_COUNT_PRODUCTS_MAKE_SELECTION').'\');}else{Joomla.submitbutton(\'phocacartcategory.countproducts\');}" class="btn btn-small button-plus"><i class="icon-plus" title="'.Text::_($this->t['l'].'_COUNT_PRODUCTS').'"></i> '.Text::_($this->t['l'].'_COUNT_PRODUCTS').'</button></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml, 'countproducts');




		// Catalog JS
		if ($printed_catalog_enable == 1) {
			Factory::getDocument()->addScriptDeclaration('

function phOpenCatalog(href){
	var categories = [];
	jQuery("input:checkbox[name=\'cid[]\']:checked").each(function(){
	    categories.push(parseInt(jQuery(this).val()));
    });

    if (categories === undefined || categories.length == 0) {
        alert(\'' . Text::_('COM_PHOCACART_WARNING_CATALOG_MAKE_SELECTION') . '\');
        return false;
    } else {
        
        var categoriesString = categories.join(",");
        href = href + "&cid=" + categoriesString;
		window.open(href, \'catalog\', \'width=880,height=560,scrollbars=yes,menubar=no,resizable=yes\'); return false;
	}
}'
			);

			// Catalog HTML
			$linkTxt = Route::_('index.php?option=com_phocacart&view=phocacartcatalogs&tmpl=component&format=raw&' . Session::getFormToken() . '=1');
			$linkTxtHandler = 'onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\'' . Text::_('COM_PHOCACART_WARNING_CATALOG_MAKE_SELECTION') . '\');return false;}else{phOpenCatalog(this.href);return false;}"';

			// Catalog PDF
			$dhtml = '<joomla-toolbar-button><a href="' . $linkTxt . '" class="btn btn-small btn-primary" ' . $linkTxtHandler . '><i id="ph-icon-text" class="icon-dummy ' . $this->s['i']['list-alt'] . ' ph-icon-text"></i>' . Text::_('COM_PHOCACART_CREATE_CATALOG_HTML') . '</a></joomla-toolbar-button>';
			$bar->appendButton('Custom', $dhtml, 'countproducts');

			$this->t['plugin-pdf'] = PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
			$this->t['component-pdf'] = PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');
			if ($this->t['plugin-pdf'] == 1 && $this->t['component-pdf']) {
				$linkPdf = Route::_('index.php?option=com_phocacart&view=phocacartcatalogs&tmpl=component&format=pdf&' . Session::getFormToken() . '=1');
				$linkPdfHandler = 'onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\'' . Text::_('COM_PHOCACART_WARNING_CATALOG_MAKE_SELECTION') . '\');return false;}else{phOpenCatalog(this.href);return false;}"';
				$dhtml = '<joomla-toolbar-button><a href="' . $linkPdf . '" class="btn btn-small btn-danger" ' . $linkPdfHandler . '><i id="ph-icon-pdf" class="icon-dummy ' . $this->s['i']['list-alt'] . ' ph-icon-pdf"></i>' . Text::_('COM_PHOCACART_CREATE_CATALOG_PDF') . '</a></joomla-toolbar-button>';
				$bar->appendButton('Custom', $dhtml);

			}
		}


		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );

		PhocacartRenderAdminview::renderWizardButton('back');

	}

	protected function processTree( $data, $tree, $id = 0, $text='', $currentId = 0, $level = 0, $parentsTreeString = '', $maxLevel = false) {


		$countItemsInCat 	= 0;// Ordering
		$level 				= $level + 1;
		$parentsTreeString	= $id . ' '. $parentsTreeString;

		// Limit the level of tree
		if (!$maxLevel || ($maxLevel && $level < $maxLevel)) {
			foreach ($data as $key) {
				$show_text 	= $text . $key->title;

				static $iCT = 0;// All displayed items

				if ($key->parent_id == $id && $currentId != $id && $currentId != $key->id ) {
					$tree[$iCT] 					= new CMSObject();

					// Ordering MUST be solved here
					if ($countItemsInCat > 0) {
						$tree[$iCT]->orderup				= 1;
					} else {
						$tree[$iCT]->orderup 				= 0;
					}

					if ($countItemsInCat < ($key->countid - 1)) {
						$tree[$iCT]->orderdown 				= 1;
					} else {
						$tree[$iCT]->orderdown 				= 0;
					}

					$tree[$iCT] 					= new CMSObject();

					$tree[$iCT]->level				= $level;
					$tree[$iCT]->parentstree		= $parentsTreeString;

					$tree[$iCT]->id 				= $key->id;
					$tree[$iCT]->title 				= $show_text;
					$tree[$iCT]->title_self 		= $key->title;
					$tree[$iCT]->parent_id			= $key->parent_id;
					$tree[$iCT]->alias				= $key->alias;
					$tree[$iCT]->image				= $key->image;
					$tree[$iCT]->description		= $key->description;
					$tree[$iCT]->published			= $key->published;
					$tree[$iCT]->editor				= $key->editor;
					$tree[$iCT]->ordering			= $key->ordering;
					$tree[$iCT]->access				= $key->access;
					$tree[$iCT]->access_level		= $key->access_level;
					$tree[$iCT]->count				= $key->count;
					$tree[$iCT]->params				= $key->params;
					$tree[$iCT]->checked_out		= $key->checked_out;
					$tree[$iCT]->checked_out_time	= $key->checked_out_time;
					$tree[$iCT]->groupname			= 0;
				//	$tree[$iCT]->username			= $key->username;
				//	$tree[$iCT]->usernameno			= $key->usernameno;
					$tree[$iCT]->parentcat_title	= $key->parentcat_title;
					$tree[$iCT]->parentcat_id		= $key->parentcat_id;
					$tree[$iCT]->hits				= $key->hits;
				//	$tree[$iCT]->ratingavg			= $key->ratingavg;
				//	$tree[$iCT]->accessuserid		= $key->accessuserid;
				//	$tree[$iCT]->uploaduserid		= $key->uploaduserid;
					$tree[$iCT]->association		= isset($key->association) ? $key->association : 0;
					$tree[$iCT]->language			= $key->language;
					$tree[$iCT]->language_title		= $key->language_title;
					$tree[$iCT]->language_image		= $key->language_image;
					$tree[$iCT]->count_date			= $key->count_date;
					$tree[$iCT]->count_products		= $key->count_products;
				//	$tree[$iCT]->deleteuserid		= $key->deleteuserid;
				//	$tree[$iCT]->userfolder			= $key->userfolder;
				//	$tree[$iCT]->approved			= $key->approved;
				//	$tree[$iCT]->link				= '';
				//	$tree[$iCT]->filename			= '';// Will be added in View (after items will be reduced)
				//	$tree[$iCT]->linkthumbnailpath	= '';

					$iCT++;
					$tree = $this->processTree($data, $tree, $key->id, $show_text . " - ", $currentId, $level, $parentsTreeString, $maxLevel);
					$countItemsInCat++;
				}
			}
		}
		return($tree);
	}

	protected function getSortFields() {
		return array(
			'a.ordering'	=> Text::_('JGRID_HEADING_ORDERING'),
			'a.title' 		=> Text::_($this->t['l'] . '_TITLE'),
			'a.published' 	=> Text::_($this->t['l'] . '_PUBLISHED'),
			'parent_title' 	=> Text::_($this->t['l'] . '_PARENT_CATEGORY'),
			'a.count_products' 	=> Text::_($this->t['l'] . '_PRODUCT_COUNT'),
			'language' 		=> Text::_('JGRID_HEADING_LANGUAGE'),
			'a.hits' 		=> Text::_($this->t['l'] . '_HITS'),
			'a.id' 			=> Text::_('JGRID_HEADING_ID')
		);
	}
}
?>
