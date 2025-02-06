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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Router\Route;
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
		$this->t['report_type'] = $this->state->get('filter.report_type', 0);
		$this->t['order_status'] = $this->state->get('filter.order_status', 0);

		$this->params			= PhocacartUtils::getComponentParameters();
		$app				= Factory::getApplication();
		$this->t['format']	= $app->input->get('format', '', 'string');

		if (!empty($this->t['date_days'])) {
			$count	= iterator_count($this->t['date_days']);
		} else {
			$count = 0;
		}

		$this->t['data_error'] 			= 0;
		$this->t['data_possible_days'] 	= PhocacartUtilsSettings::getReportLimitDays();
		if ($count > (int)$this->t['data_possible_days']) {
			$this->state->set('filter.date_to', '');
			$this->state->set('filter.date_from', '');
			$this->t['data_error'] = 1;
			$this->t['data_error_message'] = Text::_('COM_PHOCACART_SELECT_INTERVAL_THAT_HAS_FEWER_DAYS_THAN_LIMIT'). ' '. Text::_('COM_PHOCACART_LIMIT_IS'). ': '.$this->t['data_possible_days'];
		}

		if ($this->t['data_error'] == 0) {

			switch ($this->t['report_type']) {

				case 2:


					$this->items	= $this->get('Items');
					$this->total	= false;

				break;

				case 0:
				case 1:
				default:

					$items				= $this->get('Items');
					$orderCalc 			= new PhocacartOrderCalculation();
					$orderCalc->calculateOrderItems($items);
					$this->items		= $orderCalc->getItems();
					$this->total		= $orderCalc->getTotal();
					$this->currencies 	= $orderCalc->getCurrencies();


				break;

			}


		}

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		parent::display('report');
	}
}
?>
