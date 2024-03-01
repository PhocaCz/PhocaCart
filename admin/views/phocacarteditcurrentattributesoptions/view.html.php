<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocaCartCpViewPhocaCartEditCurrentAttributesOptions extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $id;
	protected $items = [];

	function display($tpl = null) {

		$db = Factory::getDBO();
		$app								= Factory::getApplication();
		$this->p['id']						= $app->input->get('id', 0, 'int');
		$this->p['cid']						= $app->input->get('cid', '', 'string'); // comma separated categories
		$this->p['typeview']				= $app->input->get('typeview', 'attribute', 'string');
		$this->p['parentattributealias']	= $app->input->get('parentattributealias', '', 'string');
		$this->p['parentattributetitle']	= $app->input->get('parentattributetitle', '', 'string');
		$this->p['field']					= $app->input->get('field', '', 'string');
		$this->p['fieldparent']				= $app->input->get('fieldparent', '', 'string');

		$this->p['parentattributetitle'] = PhocacartText::filterValue($this->p['parentattributetitle'], 'text');
		$this->p['parentattributealias'] = PhocacartText::filterValue($this->p['parentattributealias'], 'text');
		$this->p['cid'] = PhocacartText::filterValue($this->p['cid'], 'number4');// only number and ,

		$this->t			= PhocacartUtils::setVars('cart');
		$this->r 			= new PhocacartRenderAdminviews();

		if ($this->p['typeview'] == 'attribute') {

            // POSSIBLE FEATURE - limit to categories (left join product_id + categories $this->p['cid'])
            $query = 'SELECT a.id, a.title, a.alias';

            if (I18nHelper::isI18n()) {
                foreach (I18nHelper::getI18nLanguages() as $language) {
                    $query .= ', coalesce(i18n_' . $language->lang_id . '.title, a.title) AS title_' . $language->lang_id;
                    $query .= ', coalesce(i18n_' . $language->lang_id . '.alias, a.alias) AS alias_' . $language->lang_id;
                }
            }

            $query .= ' FROM #__phocacart_attributes AS a';

            if (I18nHelper::isI18n()) {
                foreach (I18nHelper::getI18nLanguages() as $language) {
                    $query .= ' LEFT JOIN #__phocacart_attributes_i18n AS i18n_' . $language->lang_id . ' ON i18n_' . $language->lang_id . '.id = a.id AND i18n_' . $language->lang_id . '.language = ' . $db->quote($language->lang_code);
                }
            }

            $query .= ' WHERE a.published = 1'
                . ' GROUP BY a.alias'
                . ' ORDER BY a.title';
            $db->setQuery($query);

            $this->items = $db->loadAssocList();
        } else if ($this->p['typeview'] == 'option') {

			if ($this->p['parentattributealias'] != '') {
                $query = 'SELECT o.*';

				if (I18nHelper::isI18n()) {
                    foreach (I18nHelper::getI18nLanguages() as $language) {
                        $query .= ', coalesce(i18n_' . $language->lang_id . '.title, o.title) AS title_' . $language->lang_id;
                        $query .= ', coalesce(i18n_' . $language->lang_id . '.alias, o.alias) AS alias_' . $language->lang_id;
                    }
                }

                $query .= ' FROM #__phocacart_attribute_values AS o'
                    . ' LEFT JOIN #__phocacart_attributes AS a ON a.id = o.attribute_id';

                if (I18nHelper::isI18n()) {
                    foreach (I18nHelper::getI18nLanguages() as $language) {
                        $query .= ' LEFT JOIN #__phocacart_attribute_values_i18n AS i18n_' . $language->lang_id . ' ON i18n_' . $language->lang_id . '.id = o.id AND i18n_' . $language->lang_id . '.language = ' . $db->quote($language->lang_code);
                    }
                }

                $query .= ' WHERE a.alias = ' . $db->quote($this->p['parentattributealias']) . ' AND o.published = 1'
                    . ' GROUP BY o.alias'
                    . ' ORDER BY o.title';

				$db->setQuery($query);
				$this->items = $db->loadAssocList();
			}
		}

		$media = new PhocacartRenderAdminmedia();

		parent::display($tpl);
	}
}
