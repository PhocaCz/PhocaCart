<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\HTMLHelper;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartItems extends HtmlView
{

	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
    public $filterForm;
	public $activeFilters;

	function display($tpl = null) {


		//if ($this->getLayout() !== 'modal') {
		//	ContactHelper::addSubmenu('phocacartitems');
		//}

		$this->t			= PhocacartUtils::setVars('item');
		$this->r 			= new PhocacartRenderAdminviews();
		$this->items			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->filterForm   	= $this->get('FilterForm');
        $this->activeFilters 	= $this->get('ActiveFilters');


        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}


        $paramsC = PhocacartUtils::getComponentParameters();
        $this->t['admin_columns_products'] = $paramsC->get('admin_columns_products', 'sku=E, image, title, published, categories, price=E, price_original=E, stock=E, access_level, language, association, hits, id');
        $this->t['admin_columns_products'] = explode(',', $this->t['admin_columns_products']);


		// Multiple categories, ordering
		$this->t['catid']	= $this->escape($this->state->get('filter.category_id'));
		$this->t['ordering']= false;// Is specific ordering used (ordering in phocacart_product_categories reference table)
		if (isset($this->t['catid']) && (int)$this->t['catid'] > 0) {
			$this->t['ordering']= true;
		}

		// Multiple categories: Ordering and list all ids on the site ($idItems)
		$idItems			= array();
		foreach ($this->items as &$item) {
			if (isset($this->t['catid']) && (int)$this->t['catid'] > 0) {

				$this->ordering[(int)$this->t['catid']][$item->ordering] = $item->id;
			}
			$idItems[] = $item->id;
		}

		// Make list of categories for each product (don't run group_concat alternative but own sql)
		$categories	= PhocacartCategoryMultiple::getCategoriesByProducts($idItems);

		$this->t['categories'] = array();
		if (!empty($categories)) {
			foreach ($categories as $k => $v) {
				$id = $v['product_id'];
				$this->t['categories'][$id][$k]['id'] = $v['id'];
				$this->t['categories'][$id][$k]['alias'] = $v['alias'];
				$this->t['categories'][$id][$k]['title'] = $v['title'];
			}
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
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.item_id'));
		$user  	= Factory::getUser();
		$bar 	= Toolbar::getInstance('toolbar');

		ToolbarHelper::title( Text::_($this->t['l'].'_PRODUCTS'), 'folder-close' );
		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew( $this->t['task'].'.add','JTOOLBAR_NEW');

		}
		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state')) {
			ToolbarHelper::divider();
			ToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			ToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::custom($this->t['tasks'].'.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
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

			HTMLHelper::_('bootstrap.renderModal', 'collapseModalCA');
			$title = Text::_('COM_PHOCACART_COPY_ATTRIBUTES');
			$dhtml = "<joomla-toolbar-button><button data-bs-toggle=\"modal\" data-bs-target=\"#collapseModalCA\" class=\"btn btn-small\">
						<span class=\"icon-checkbox-partial\" title=\"$title\"></span>
						$title</button></joomla-toolbar-button>";
			$bar->appendButton('Custom', $dhtml, 'copy_attributes');
		}

		$dhtml = '<joomla-toolbar-button><button class="btn btn-small" onclick="javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_MAKE_SELECTION').'\');}else{if(confirm(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_THUMBNAILS').'\')){Joomla.submitbutton(\'phocacartitem.recreate\');}}" ><i class="icon-image" title="'.Text::_('COM_PHOCACART_RECREATE_THUMBS').'"></i> '.Text::_('COM_PHOCACART_RECREATE_THUMBS').'</button></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml);

		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );

		PhocacartRenderAdminview::renderWizardButton('back');
	}

}
?>
