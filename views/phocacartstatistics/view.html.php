<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.view' );
 
class PhocaCartCpViewPhocaCartStatistics extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	
	function display($tpl = null) {
	
		$document				= JFactory::getDocument();
		$this->t				= PhocaCartUtils::setVars('statistic');
		$this->state			= $this->get('State');
		$this->t['date_from'] 	= $this->state->get('filter.date_from', PhocaCartDate::getCurrentDate(30));
		$this->t['date_to'] 	= $this->state->get('filter.date_to', PhocaCartDate::getCurrentDate());
		
		$dateDays = PhocaCartDate::getDateDays($this->t['date_from'], $this->t['date_to']);
		if (!empty($dateDays)) {
			$count	= iterator_count($dateDays);
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
			$this->items		= $this->get('Items');
			//$this->pagination	= $this->get('Pagination');
		}
		

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$dataGraph 	= '';
		$amount		= array();
		$orders		= array();
		if (!empty($this->items) && !empty($dateDays)) {
			foreach($dateDays as $date) {
				$amount[ $date->format('Y-m-d') ] = 0;
				$orders[ $date->format('Y-m-d') ] = 0;
			}
			foreach($this->items as $k => $v) {
			
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
			$dataAmount .= '["'.$i.'",'.$v.']';
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
			$dataOrders .= '["'.$i.'",'.$v.']';
			$dataTicks .= '['.$i.',"'.$k.'"]';
			$i++;
		}
		
		
		JHTML::stylesheet( $this->t['s'] );
		
		JHtml::_('jquery.framework', false);
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.flot.min.js');
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.flot.axislabels.js');
		$s	= array();
		$s[]= 'jQuery(document).ready(function () {';
		$s[]= '
var graphData = [{
		// Total Amount
        data: [ '.$dataAmount.' ],
		//label: "Total Amount",
		yaxis: 2,
        color: "#71c73e",
		labelColor: "#000"
    }, {
        // Total Orders
        data: [ '.$dataOrders.'],
		//label: "Total Orders",
		yaxis: 1,
		color: "#77b7c5",
        points: { radius: 4, fillColor: "#77b7c5" }
    }
];
		
		
// Lines
jQuery.plot(jQuery(\'#graph-lines\'), graphData, {
    series: {
        points: {
            show: true,
            radius: 5
        },
        lines: {
            show: true
        },
        shadowSize: 2
    },
    grid: {
        color: "#f0f0f0",
        borderColor: "transparent",
        borderWidth: 20,
        hoverable: true
    },
    xaxis: {
        tickColor: "#fcfcfc",
		ticks: ['.$dataTicks.']
    },
    yaxes: [{
        position: "right",
        color: "#f0f0f0",
		tickDecimals: 0,
		axisLabel: "Total Orders",
		axisLabelUseCanvas: true,
        axisLabelPadding: 10,
		axisLabelColour: "#999999"
    }, {
		position: "left",
        color: "#f0f0f0",
		tickDecimals: 2,
		axisLabel: "Total Amount",
		axisLabelUseCanvas: true,
        axisLabelPadding: 10,
		axisLabelColour: "#999999"
	}]
});
 
// Bars
jQuery.plot(jQuery(\'#graph-bars\'), graphData, {
    series: {
        bars: {
            show: true,
            barWidth: .9,
            align: \'center\'
        },
        shadowSize: 2
    },
    grid: {
        color: \'#f0f0f0\',
        borderColor: \'transparent\',
        borderWidth: 20,
        hoverable: true
    },
    xaxis: {
        tickColor: "#fcfcfc",
		ticks: ['.$dataTicks.']
    },
    yaxes: [{
        position: "right",
        color: "#f0f0f0",
		tickDecimals: 0,
		axisLabel: "'.JText::_('COM_PHOCACART_TOTAL_ORDERS', true).'",
		axisLabelUseCanvas: true,
        axisLabelPadding: 10,
		axisLabelColour: "#999999"
    }, {
		position: "left",
        color: "#f0f0f0",
		tickDecimals: 2,
		axisLabel: "'.JText::_('COM_PHOCACART_TOTAL_AMOUNT', true).'",
		axisLabelUseCanvas: true,
        axisLabelPadding: 10,
		axisLabelColour: "#999999"
	}]
});


jQuery(\'#graph-bars\').hide();
 
jQuery(\'#lines\').on(\'click\', function (e) {
    jQuery(\'#bars\').removeClass(\'active\');
    jQuery(\'#graph-bars\').fadeOut();
    jQuery(this).addClass(\'active\');
    jQuery(\'#graph-lines\').fadeIn();
    e.preventDefault();
});
 
jQuery(\'#bars\').on(\'click\', function (e) {
    jQuery(\'#lines\').removeClass(\'active\');
    jQuery(\'#graph-lines\').fadeOut();
    jQuery(this).addClass(\'active\');
    jQuery(\'#graph-bars\').fadeIn().removeClass(\'hidden\');
    e.preventDefault();
});


function showTooltip(x, y, contents) {
    jQuery(\'<div id="tooltip">\' + contents + \'</div>\').css({
        top: y - 16,
        left: x + 20
    }).appendTo(\'body\').fadeIn();
}
 
var previousPoint = null;
 
jQuery(\'#graph-lines, #graph-bars\').bind(\'plothover\', function (event, pos, item) {
    if (item) {
		console.log(item.series.yaxis.options.axisLabel);
        if (previousPoint != item.dataIndex) {
            previousPoint = item.dataIndex;
            jQuery(\'#tooltip\').remove();
            var x = item.datapoint[0],
                y = item.datapoint[1];
				
				if (item.series.yaxis.options.axisLabel == "'.JText::_('COM_PHOCACART_TOTAL_AMOUNT').'") {
					showTooltip(item.pageX, item.pageY, item.series.yaxis.options.axisLabel + ": " + y.toFixed(2) );
				} else {
					showTooltip(item.pageX, item.pageY, item.series.yaxis.options.axisLabel + ": " + y );
				}
                
        }
    } else {
        jQuery(\'#tooltip\').remove();
        previousPoint = null;
    }
});';

	$s[]= '});';
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
		JHTML::stylesheet( 'media/com_phocacart/css/graph.css' );
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	function addToolbar() {
	
		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.statistic_id'));

		JToolBarHelper::title( JText::_( $this->t['l'].'_STATISTICS' ), 'chart' );
		
		// This button is unnecessary but it is displayed because Joomla! design bug
		$bar = JToolBar::getInstance( 'toolbar' );
		$dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-small"><i class="icon-home-2" title="'.JText::_('COM_PHOCACART_CONTROL_PANEL').'"></i> '.JText::_('COM_PHOCACART_CONTROL_PANEL').'</a>';
		$bar->appendButton('Custom', $dhtml);
		
	/*
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
		}
	
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {

			JToolBarHelper::divider();
			JToolBarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}
	
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartlogs.delete', $this->t['l'].'_DELETE');
		}*/
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.'.$this->t['c'], true );
	}
	
	protected function getSortFields() {
		return array(
			'a.ordering'		=> JText::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> JText::_($this->t['l'] . '_TITLE'),
			'a.published' 		=> JText::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> JText::_('JGRID_HEADING_ID')
		);
	}
}
?>