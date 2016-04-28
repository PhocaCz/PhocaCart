<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartRenderMedia
{
	public $jquery			= 0;
	protected $document		= false;

	public function __construct() {
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		JHtml::_('jquery.framework', false);
		$this->document	= JFactory::getDocument();
	}
	
	public function loadBootstrap($load = 0) {
		if ($load == 1) {
			JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
	}
	
	public function loadChosen($load = 0) {
		if ($load == 1) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/chosen/chosen.jquery.min.js');
			$js = "\n". 'jQuery(document).ready(function(){'."\n";
			$js .= '   jQuery(".chosen-select").chosen({disable_search_threshold: 10});'."\n"; // Set chosen, created hidden will be required
			$js .= '   jQuery(".chosen-select").attr(\'style\',\'display:visible; position:absolute; clip:rect(0,0,0,0)\');'."\n";
			$js .= '});'."\n";
			$this->document->addScriptDeclaration($js);
			JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen.css' );
			JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen-bootstrap.css' );
		}
	}
	
	public function loadEqualHeights($load = 0) {
		if ($load == 1) {		
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.equalheights.min.js');
			$this->document->addScriptDeclaration(
			'jQuery(window).load(function(){
				jQuery(\'.ph-thumbnail\').equalHeights();
			});');
		}
	}
	
	public function loadSwapImage($load = 0) {
		if ($load = 0 == 1) {
			$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/swap.image.js');
		}
	}
	
	public function loadRating() {
		JHTML::stylesheet( 'media/com_phocacart/js/barrating/css/rating.css' );
		$this->document->addScript(JURI::root(true).'/media/com_phocacart/js/barrating/jquery.barrating.js');
		$js = "\n". 'jQuery(document).ready(function(){'."\n";
		$js .= 	'   jQuery(\'#phitemrating\').barrating({ showSelectedRating:false });'."\n";		
		$js .= '});'."\n";
		$this->document->addScriptDeclaration($js);
	}
}
?>