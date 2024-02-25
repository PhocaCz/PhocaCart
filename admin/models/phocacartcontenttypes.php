<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Phoca\PhocaCart\I18n\I18nListModelTrait;

class PhocaCartCpModelPhocacartContentTypes extends ListModel
{
	protected $option 	= 'com_phocacart';

    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'context', 'a.context',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'access', 'a.access', 'access_level',
                'ordering', 'a.ordering',
                'published','a.published',
            );
        }
        parent::__construct($config);
    }

	protected function populateState($ordering = 'a.ordering', $direction = 'ASC') {
		$app = Factory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);

        $state = $app->getUserStateFromRequest($this->context.'.filter.context', 'filter_context', '', 'string');
        $this->setState('filter.context', $state);

		// Load the parameters.
		$params = PhocacartUtils::getComponentParameters();
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.context');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
    {
		$db		= $this->getDatabase();
		$query	= $db->getQuery(true)
            ->select('a.*')
            ->from('`#__phocacart_content_types` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Filter by published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('a.published IN (0, 1)');
		}

        // Filter by context
        $context = $this->getState('filter.context');
        if ($context) {
           $query->where('a.context = ' . $db->quote($context));
        }

		// Filter by search in title
		$search = $this->getState('filter.search');
		if ($search) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		$orderCol	= $this->state->get('list.ordering', 'a.ordering');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

}

