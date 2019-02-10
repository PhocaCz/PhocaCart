<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layout 	= new JLayoutFile('report', null, array('component' => 'com_phocacart'));
if (!empty($this->items) && !empty($this->t['date_days'])) {
	$d				= array();
	$d['items']		= $this->items;
	$d['total']		= $this->total;
	$d['params']	= $this->params;
	$d['date_from']	= $this->t['date_from'];
	$d['date_to']	= $this->t['date_to'];
	$d['format']	= $this->t['format'];

/*	if ($d['format'] == 'raw') {
		header('Content-Type: text/txt');
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . 'report.html');
	} else if ($d['format'] == 'pdf') {
		/*header('Content-Type: application/pdf');
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . 'report.pdf'); */

	//}

	/*if ($d['format'] == 'raw') {

		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		//header('Content-Length: ' . $size);

	}*/
	if ($d['format'] == 'raw') {
		echo '<html><head><title>'.JText::_('COM_PHOCACART_REPORT').'</title></head><body>';
	}
	echo $layout->render($d);
	if ($d['format'] == 'raw') {
		echo '</body></html>';
	}

	//exit;
}
?>
