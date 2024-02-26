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
use Phoca\PhocaCart\I18n\I18nHelper;

jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocaCartItems extends HtmlView
{

	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
    public $filterForm;
    public $batchForm;
	public $activeFilters;

	function display($tpl = null) {
		$this->t			    = PhocacartUtils::setVars('item');
		$this->r 			    = new PhocacartRenderAdminviews();
		$this->items			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->filterForm   	= $this->get('FilterForm');
        $this->batchForm   	    = $this->get('BatchForm');
        $this->activeFilters 	= $this->get('ActiveFilters');


        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
		}


        $paramsC = PhocacartUtils::getComponentParameters();
        $this->t['admin_columns_products'] = $paramsC->get('admin_columns_products', 'sku=E, image, title, published, categories, price=E, price_original=E, stock=E, access_level, language, association, hits, id');
        $this->t['admin_columns_products'] = explode(',', $this->t['admin_columns_products']);
        if (I18nHelper::isI18n()) {
            $this->t['admin_columns_products'] = array_filter($this->t['admin_columns_products'], function($column) {
              return !preg_match('~^\s*language~', $column);
            });
        }


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

		ToolbarHelper::title( Text::_($this->t['l'].'_PRODUCTS'), 'archive' );
		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew( $this->t['task'].'.add','JTOOLBAR_NEW');

		}

		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}

		$dropdown = $bar->dropdownButton('status-group')->text('JTOOLBAR_CHANGE_STATUS')->toggleSplit(false)->icon('icon-ellipsis-h')->buttonClass('btn btn-action');
		$childBar = $dropdown->getChildToolbar();

		if ($canDo->get('core.edit.state')) {
			$childBar->publish($this->t['tasks'].'.publish')->listCheck(true);
			$childBar->unpublish($this->t['tasks'].'.unpublish')->listCheck(true);
			$childBar->archive($this->t['tasks'].'.archive')->listCheck(true);
			$childBar->standardButton('featured')->text('JFEATURE')->task($this->t['tasks'].'.featured')->listCheck(true);
			$childBar->standardButton('unfeatured')->text('JUNFEATURE')->task($this->t['tasks'].'.unfeatured')->listCheck(true);
            if ($this->state->get('filter.published') != -2) {
                $childBar->trash($this->t['tasks'] . '.trash')->text($this->t['l'] . '_TRASH')->icon('icon-trash')->listCheck(true);
            }
		}

		if ($canDo->get('core.delete')) {
            if ($this->state->get('filter.published') == -2) {
                $childBar->delete($this->t['tasks'] . '.delete')->text($this->t['l'] . '_DELETE')->message($this->t['l'] . '_WARNING_DELETE_ITEMS')->icon('icon-trash')->listCheck(true);
            }
		}

		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			HTMLHelper::_('bootstrap.renderModal', 'collapseModal');
			$childBar->popupButton('batch')->text('JTOOLBAR_BATCH')->selector('collapseModal');

			HTMLHelper::_('bootstrap.renderModal', 'collapseModalCA');
			$childBar->popupButton('copy_attributes')->text('COM_PHOCACART_COPY_ATTRIBUTES')->selector('collapseModalCA')->icon('icon-list')->listCheck(true);
		}

		$onClick = 'javascript:if(document.adminForm.boxchecked.value==0){alert(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_MAKE_SELECTION').'\');}else{if(confirm(\''.Text::_('COM_PHOCACART_WARNING_RECREATE_THUMBNAILS').'\')){Joomla.submitbutton(\'phocacartitem.recreate\');}}';

		$childBar->standardButton('recreate')->text('COM_PHOCACART_RECREATE_THUMBS')->onclick($onClick)->icon('icon-image')->listCheck(true);

		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );

		PhocacartRenderAdminview::renderWizardButton('back');
	}

}
