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

$layout 	= new JLayoutFile('catalog', null, array('component' => 'com_phocacart'));
if (!empty($this->items)) {
	$d				= array();
	$d['s']         = $this->s;
	$d['items']		= $this->items;
	$d['params']	= $this->params;
	$d['format']	= $this->t['format'];

	echo $layout->render($d);
	//exit;
}
?>
