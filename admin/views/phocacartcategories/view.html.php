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
    public $batchForm;

	function display($tpl = null) {
		$this->t = PhocacartUtils::setVars('category');
		$this->r = new PhocacartRenderAdminviews();
		$this->s = PhocacartRenderStyle::getStyles();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
        $this->batchForm = $this->get('BatchForm');

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[$item->parent_id][] = $item->id;
		}

		// if search, don't do a tree, only display the searched items
		$this->t['search'] = $this->state->get('filter.search');
		$this->t['level'] = $this->state->get('filter.level');

		$media = new PhocacartRenderAdminmedia();

		// ASSOCIATION
		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		} else {
			if ($forcedLanguage = Factory::getApplication()->input->getCmd('forcedLanguage')) {
				$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);
				unset($this->activeFilters['language']);
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
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


		$dropdown = $bar->dropdownButton('status-group')->text('JTOOLBAR_CHANGE_STATUS')->toggleSplit(false)->icon('icon-ellipsis-h')->buttonClass('btn btn-action')->listCheck(true);
		$childBar = $dropdown->getChildToolbar();


		if ($canDo->get('core.edit.state')) {

			//ToolbarHelper::divider();
			$childBar->publish($this->t['tasks'].'.publish')->listCheck(true);
			$childBar->unpublish($this->t['tasks'].'.unpublish')->listCheck(true);
			$childBar->standardButton('featured')->text('JFEATURE')->task($this->t['tasks'].'.featured')->listCheck(true);
			$childBar->standardButton('unfeatured')->text('JUNFEATURE')->task($this->t['tasks'].'.unfeatured')->listCheck(true);

			//ToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			//ToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($canDo->get('core.delete')) {
			$childBar->delete($this->t['tasks'].'.delete')->text($this->t['l'].'_DELETE')->message( $this->t['l'].'_WARNING_DELETE_ITEMS')->icon('icon-trash')->listCheck(true);
			//ToolbarHelper::deleteList( Text::_( $this->t['l'].'_WARNING_DELETE_ITEMS' ), $this->t['tasks'].'.delete', $this->t['l'].'_DELETE');
		}

		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			HTMLHelper::_('bootstrap.renderModal', 'collapseModal');
			/*$title = Text::_('JTOOLBAR_BATCH');
			$dhtml = "<joomla-toolbar-button><button data-bs-toggle=\"modal\" data-bs-target=\"#collapseModal\" class=\"btn btn-small\">
						<span class=\"icon-checkbox-partial\" title=\"$title\"></span>
						$title</button></joomla-toolbar-button>";
			$bar->appendButton('Custom', $dhtml, 'batch');*/
			$childBar->popupButton('batch')->text('JTOOLBAR_BATCH')->selector('collapseModal')->listCheck(true);
		}

		/*$dhtml = '<joomla-toolbar-button><button class="btn btn-primary btn-small" onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_MAKE_SELECTION').'\');}else{if(confirm(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_THUMBNAILS_CATEGORIES').'\')){Joomla.submitbutton(\'phocacartcategory.recreate\');}}" ><i class="icon-image" title="'.Text::_('COM_PHOCACART_RECREATE_THUMBS').'"></i> '.Text::_('COM_PHOCACART_RECREATE_THUMBS').'</button></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml);*/

		$onClick = 'javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_MAKE_SELECTION').'\');}else{if(confirm(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_THUMBNAILS_CATEGORIES').'\')){Joomla.submitbutton(\'phocacartcategory.recreate\');}}';
		$childBar->standardButton('recreate')->text('COM_PHOCACART_RECREATE_THUMBS')->onclick($onClick)->icon('icon-image');



		/*$dhtml = '<joomla-toolbar-button><button onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_COUNT_PRODUCTS_MAKE_SELECTION').'\');}else{Joomla.submitbutton(\'phocacartcategory.countproducts\');}" class="btn btn-small button-plus"><i class="icon-plus" title="'.Text::_($this->t['l'].'_COUNT_PRODUCTS').'"></i> '.Text::_($this->t['l'].'_COUNT_PRODUCTS').'</button></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml, 'countproducts');*/

		$onClick = 'javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_COUNT_PRODUCTS_MAKE_SELECTION').'\');}else{Joomla.submitbutton(\'phocacartcategory.countproducts\');}';
		$childBar->standardButton('countproducts')->text($this->t['l'].'_COUNT_PRODUCTS')->onclick($onClick)->icon('icon-plus');




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
			/*$linkTxtHandler = 'onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\'' . Text::_('COM_PHOCACART_WARNING_CATALOG_MAKE_SELECTION') . '\');return false;}else{phOpenCatalog(this.href);return false;}"';

			// Catalog PDF
			$dhtml = '<joomla-toolbar-button><a href="' . $linkTxt . '" class="btn btn-small btn-primary" ' . $linkTxtHandler . '><i id="ph-icon-text" class="icon-dummy ' . $this->s['i']['list-alt'] . ' ph-icon-text"></i>' . Text::_('COM_PHOCACART_CREATE_CATALOG_HTML') . '</a></joomla-toolbar-button>';
			$bar->appendButton('Custom', $dhtml, 'countproducts');*/


			$onClick = 'javascript:if(document.adminForm.boxchecked.value==0){alert(\'' . Text::_('COM_PHOCACART_WARNING_CATALOG_MAKE_SELECTION') . '\');return false;}else{phOpenCatalog(this.dataset.href);return false;}';
		$childBar->standardButton('phocacartcatalogs')->text('COM_PHOCACART_CREATE_CATALOG_HTML')->attributes(['data-href' => $linkTxt])->onclick($onClick)->icon('icon-book');





			$this->t['plugin-pdf'] = PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
			$this->t['component-pdf'] = PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');
			if ($this->t['plugin-pdf'] == 1 && $this->t['component-pdf']) {
				$linkPdf = Route::_('index.php?option=com_phocacart&view=phocacartcatalogs&tmpl=component&format=pdf&' . Session::getFormToken() . '=1');
				/*$linkPdfHandler = 'onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\'' . Text::_('COM_PHOCACART_WARNING_CATALOG_MAKE_SELECTION') . '\');return false;}else{phOpenCatalog(this.href);return false;}"';
				$dhtml = '<joomla-toolbar-button><a href="' . $linkPdf . '" class="btn btn-small btn-danger" ' . $linkPdfHandler . '><i id="ph-icon-pdf" class="icon-dummy ' . $this->s['i']['list-alt'] . ' ph-icon-pdf"></i>' . Text::_('COM_PHOCACART_CREATE_CATALOG_PDF') . '</a></joomla-toolbar-button>';
				$bar->appendButton('Custom', $dhtml);*/

				$onClick = 'javascript:if(document.adminForm.boxchecked.value==0){alert(\'' . Text::_('COM_PHOCACART_WARNING_CATALOG_MAKE_SELECTION') . '\');return false;}else{phOpenCatalog(this.dataset.href);return false;}';
				$childBar->standardButton('phocacartcatalogspdf')->text('COM_PHOCACART_CREATE_CATALOG_PDF')->attributes(['data-href' => $linkPdf])->onclick($onClick)->icon('icon-book');

			}
		}


		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );

		PhocacartRenderAdminview::renderWizardButton('back');
	}
}

