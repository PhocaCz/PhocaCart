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
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartReports extends HtmlView
{

	protected $state;
	protected $t;
	protected $r;
	protected $s;
	protected $params;
	protected $items 	= array();
	protected $total	= array();




	function display($tpl = null) {


		$document = Factory::getDocument();
		$document->setTitle(Text::_('COM_PHOCACART_REPORT'));

		$this->t				= PhocacartUtils::setVars('report');
		$this->s                = PhocacartRenderStyle::getStyles();
		$this->state			= $this->get('State');
		$this->t['date_from'] 	= $this->state->get('filter.date_from', PhocacartDate::getCurrentDate(30));
		$this->t['date_to'] 	= $this->state->get('filter.date_to', PhocacartDate::getCurrentDate());
		$this->t['date_days'] 	= PhocacartDate::getDateDays($this->t['date_from'], $this->t['date_to']);
		$this->params			= PhocacartUtils::getComponentParameters();
		$app				= Factory::getApplication();
		$this->t['format']	= $app->input->get('format', '', 'string');

		if (!empty($this->t['date_days'])) {
			$count	= iterator_count($this->t['date_days']);
		} else {
			$count = 0;
		}

		$this->t['data_error'] 			= 0;
		$this->t['data_possible_days'] 	= 365;
		if ($count > (int)$this->t['data_possible_days']) {
			$this->state->set('filter.date_to', '');
			$this->state->set('filter.date_from', '');
			$this->t['data_error'] = 1;
		}

		if ($this->t['data_error'] == 0) {

			$items				= $this->get('Items');
			$orderCalc 			= new PhocacartOrderCalculation();
			$orderCalc->calculateOrderItems($items);
			$this->items		= $orderCalc->getItems();
			$this->total		= $orderCalc->getTotal();
			$this->currencies 	= $orderCalc->getCurrencies();

		}

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}
		
		$this->document->setName(Text::_('COM_PHOCACART_REPORT'));

		parent::display('report');
	}
}
?>
