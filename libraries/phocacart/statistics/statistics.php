<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
final class PhocaCartStatistics
{
	protected $fn = array();
	
	public function __construct() {

		$document	= JFactory::getDocument();
		JHtml::_('jquery.framework', false);
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/chartjs/Chart.min.js');
		//$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.1/Chart.bundle.js');
	}

	public function renderChartJsLine($id, $dataA, $dataALabel, $dataB, $dataBLabel, $dataX) {

		$bC 	= 'rgba(151,187,205,1)';
		$baC	= 'rgba(151,187,205,0.1)';
		$pbC	= 'rgba(255,255,255,1)';
		$pbaC 	= 'rgba(151,187,205,1)';
		
		$bC2 	= 'rgba(220,220,220,1)';
		$baC2	= 'rgba(220,220,220,0.1)';
		$pbC2	= 'rgba(255,255,255,1)';
		$pbaC2 	= 'rgba(220,220,220,1)';
		
		$o = "
var config".$id." = {
type: 'line',
data: {
	datasets: [{
		data: [".$dataA."],
		yAxisID: 'y-axis-2',
		pointRadius : 4,
		borderColor : '".$bC."',
		backgroundColor : '".$baC."',
		pointBorderColor : '".$pbC."',
		pointBackgroundColor :'".$pbaC."',
		pointBorderWidth : 1,
		radius: 4,
		pointHoverRadius: 5,
		 
		label: '".htmlspecialchars($dataALabel)."',
		/*fillColor: '#fff',
		strokeColor: '#fff',
		pointColor: '#fff',
		pointStrokeColor: '#fff',
		pointHighlightFill: '#000',
		pointHighlightStroke: '#fff'*/

	}, {
	   data: [".$dataB."],
		yAxisID: 'y-axis-1',
		borderColor : '".$bC2."',
		backgroundColor : '".$baC2."',
		pointBorderColor : '".$pbC2."',
		pointBackgroundColor :'".$pbaC2."',
		pointBorderWidth : 1,
		radius: 4,
		pointHoverRadius: 5,
		
		label: '".htmlspecialchars($dataBLabel)."',

	}],
	labels: [".htmlspecialchars($dataX)."
	],
},
scaleIntegersOnly: true,
options: {  
		responsive: true,
		hoverMode: 'label',
		stacked: false,
		scales: {
			xAxes: [{
				display: true,
				gridLines: {
					drawOnChartArea: true,
					offsetGridLines: false,
					show: true,
					color: '#f0f0f0',
					zeroLineColor: '#f0f0f0',
					lineWidth: 1,
					/*drawOnChartArea: true,
					drawTicks: true,
					zeroLineWidth: 10,
					zeroLineColor: '#fff300'*/					
				}
			}],
			yAxes: [{
				type: 'linear',
				display: true,
				position: 'left',
				id: 'y-axis-1',
				gridLines: {
					drawOnChartArea: true,
					color: '#f0f0f0',
					zeroLineColor: '#f0f0f0',
					lineWidth: 1,
				},
				label: 'text',
				ticks: {
					callback: function(value) {
						if (value % 1 === 0) {
							return Math.floor(value);
						} else {
							return '';
						}
					}
				}
			}, {
				type: 'linear',
				display: true,
				position: 'right',
				id: 'y-axis-2',
				gridLines: {
					drawOnChartArea: false,
					color: '#f0f0f0',
					zeroLineColor: '#000',
				},
				label: 'text',
				ticks: {
					callback: function(value) {
						if (value % 1 === 0) {
							return Math.floor(value);
						} else {
							return '';
						}
					}
				}
			}],
		},
	}
};";
		JFactory::getDocument()->addScriptDeclaration($o);
	}
	
	
	public function renderChartJsPie($id, $data) {

		$colors = array('#FFCC33', '#FF6633', '#FF3366', '#FF33CC', '#CC33FF');
		$dS = $lS = $bS = '';
		if (!empty($data)) {
			
			foreach($data as $k => $v) {
				$d[$k] = '\''. $v['items']. '\'';
				$l[$k] = '\''. $v['title']. '\'';
				$c = $k%5;
			
				$b[$k] = '\''. $colors[$k]. '\'';
			}
			
			$dS = implode(',', $d);
			$lS = implode(',', $l);
			$bS = implode(',', $b);
		}

		
		$o = "
var config".$id." = {
	type: 'pie',
	data: {
		datasets: [{
			data: [".htmlspecialchars($dS)."],
			backgroundColor : [".htmlspecialchars($bS)."],
		}],
		labels: [".htmlspecialchars($lS)."]
	},
	options: {
		responsive: true,
	}
};";
		JFactory::getDocument()->addScriptDeclaration($o);
	}
	
	public function setFunction($id, $type) {
		$this->fn[$id]['id']	= $id;
		$this->fn[$id]['type']	= $type;
		$this->fn[$id]['area']	= $id;
	}
	
	public function renderFunctions() {
		
		$s	 = array();
		$s[] = 'window.onload = function() {';
		
		if (!empty($this->fn)) {
			foreach($this->fn as $k => $v) {
				$s[] = 'var ctx'.$v['id'].' = document.getElementById(\''.$v['area'].'\').getContext(\'2d\');';
				$s[] = 'window.'.$v['type'].' = new Chart(ctx'.$v['id'].', config'.$v['id'].');';
			}
		}
		$s[] = '};';
		JFactory::getDocument()->addScriptDeclaration(implode( "\n", $s ));
	}
	
	public function getDataChart($numberOfDate = '', $dateFrom = '', $dateTo = '') {
		
		
		$db	= JFactory::getDbo();
		$q	= $db->getQuery(true);

		$q->select('a.id, DATE(a.date) AS date_only, COUNT(DATE(a.date)) AS count_orders');
		$q->from('`#__phocacart_orders` AS a');
		
		$q->select('SUM(t.amount) AS order_amount');
		$q->join('LEFT', '#__phocacart_order_total AS t ON a.id=t.order_id');
		$q->where('t.type = \'brutto\'' );
	
		if ($numberOfDate == '') {
			$numberOfDate = 6; //7 days
		}
		if ($dateFrom == '') {
			$dateFrom 	= PhocaCartDate::getCurrentDate($numberOfDate);
		}
		if ($dateTo == '') {
			$dateTo 	= PhocaCartDate::getCurrentDate();
		}
		$dateDays = PhocaCartDate::getDateDays($dateFrom, $dateTo);


		if ($dateTo != '' && $dateFrom != '') {
			$dateFrom 	= $db->Quote($dateFrom);
			$dateTo 	= $db->Quote($dateTo);
			$q->where('DATE(a.date) >= '.$dateFrom.' AND DATE(a.date) <= '.$dateTo );
		}
		$q->group('DATE(a.date)');

		$q->order($db->escape('a.date ASC'));
		//echo nl2br(str_replace('#__', 'jos_', $q->__toString()));
		$db->setQuery($q);

		$items = $db->loadObjectList();


		$amount		= array();
		$orders		= array();
		if (!empty($items) && !empty($dateDays)) {
			foreach($dateDays as $date) {
				$amount[ $date->format('Y-m-d') ] = 0;
				$orders[ $date->format('Y-m-d') ] = 0;
			}
			foreach($items as $k => $v) {
			
				if (isset($amount[$v->date_only])) {
					$amount[$v->date_only] = $v->order_amount;
				}
				if (isset($orders[$v->date_only])) {
					$orders[$v->date_only] = $v->count_orders;
				}
			}
		}
	
		
		$dataAmount = '';
		$dataOrders = '';
		$dataTicks	= '';
		$i = 1;
		foreach ($amount as $k => $v) {
			if ($dataAmount != '') {
				$dataAmount .= ', ';
			}
			$dataAmount .= (int)$v;
			$i++;
		}
		$i = 1;
		foreach ($orders as $k => $v) {
			if ($dataOrders != '') {
				$dataOrders .= ', ';
			}
			if ($dataTicks != '') {
				$dataTicks .= ', ';
			}
			$dataOrders .= (int)$v;
			$dataTicks .= '\''.$k.'\'';
			
			$i++;
		}
		
		$rData				= array();
		$rData['amount']	= $dataAmount;
		$rData['orders']	= $dataOrders;
		$rData['ticks']		= $dataTicks;
		
		return $rData;
	}
	
	public function getNumberOfOrders($numberOfDate = '', $dateFrom = '', $dateTo = '') {
		
		if ($numberOfDate == '') {
			$numberOfDate = 7;
		}
		
		if ($dateFrom == '') {
			$dateFrom 	= PhocaCartDate::getCurrentDate($numberOfDate);
		}
		if ($dateTo == '') {
			$dateTo 	= PhocaCartDate::getCurrentDate();
		}
		
		$db		= JFactory::getDbo();
		$q = 'SELECT COUNT(a.id) FROM #__phocacart_orders AS a WHERE a.published = 1';
		$q .= ' AND DATE(a.date) >= '.$db->quote($dateFrom).' AND DATE(a.date) <= '.$db->quote($dateTo);
		$db->setQuery($q);
		$count = $db->loadRow();
		if (isset($count[0]) && (int)$count[0] != 0) {
			return PhocaCartStatistics::abreviateNumbers($count[0]);
		}
		return 0;
	}
	
	public function getNumberOfUsers($numberOfDate = '', $dateFrom = '', $dateTo = '') {
		
		if ($numberOfDate == '') {
			$numberOfDate = 7;
		}
		
		if ($dateFrom == '') {
			$dateFrom 	= PhocaCartDate::getCurrentDate($numberOfDate);
		}
		if ($dateTo == '') {
			$dateTo 	= PhocaCartDate::getCurrentDate();
		}
		
		$db		= JFactory::getDbo();
		$q = 'SELECT COUNT(DISTINCT(a.user_id)) FROM #__phocacart_orders AS a WHERE a.published = 1';
		$q .= ' AND DATE(a.date) >= '.$db->quote($dateFrom).' AND DATE(a.date) <= '.$db->quote($dateTo);
		
		$db->setQuery($q);
		$count = $db->loadRow();
		
		if (isset($count[0]) && (int)$count[0] != 0) {
			return PhocaCartStatistics::abreviateNumbers($count[0]);
		}
		return 0;
	}
	
	public function getAmountOfOrders($numberOfDate = '', $dateFrom = '', $dateTo = '') {
		
		if ($numberOfDate == '') {
			$numberOfDate = 7;
		}
		
		if ($dateFrom == '') {
			$dateFrom 	= PhocaCartDate::getCurrentDate($numberOfDate);
		}
		if ($dateTo == '') {
			$dateTo 	= PhocaCartDate::getCurrentDate();
		}
		
		$db		= JFactory::getDbo();
		$q = ' SELECT SUM(t.amount) FROM #__phocacart_orders AS a';
		$q .= ' LEFT JOIN #__phocacart_order_total AS t ON a.id = t.order_id';
		$q .= ' WHERE a.published = 1';
		$q .= ' AND t.type = '.$db->quote('brutto');
		$q .= ' AND DATE(a.date) >= '.$db->quote($dateFrom).' AND DATE(a.date) <= '.$db->quote($dateTo);
		$db->setQuery($q);
		$count = $db->loadRow();
		if (isset($count[0]) && (int)$count[0] != 0) {
			return $this->abreviateNumbers($count[0]);
		}
		return 0;
	}
	
	/*
	 * http://stackoverflow.com/questions/13049851/php-number-abbreviator
	 */
	
	public function abreviateNumbers($value) {
		 
		$abbreviations = array(12 => 'T', 9 => 'B', 6 => 'M', 3 => 'K', 0 => '');
		foreach($abbreviations as $exponent => $abbreviation) {
			if($value >= pow(10, $exponent)) {
				//return round(floatval($value / pow(10, $exponent))).$abbreviation;
				return round(floatval($value / pow(10, $exponent)),1).$abbreviation;
			}
		}
	}
}
?>