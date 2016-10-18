<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
if (! class_exists('JHtmlGrid')) {
	require_once( JPATH_SITE.'/libraries/joomla/html/html/grid.php' );
}
//jimport('joomla.html.html.jgrid'); 
class PhocaCartGrid extends JHtmlJGrid
{
	
}
