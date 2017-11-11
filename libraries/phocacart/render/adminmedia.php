<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

class PhocacartRenderAdminmedia
{
	public $jquery			= 0;
	protected $document		= false;
	
	public function __construct() {

		$this->document	= JFactory::getDocument();
		JHTML::_('behavior.tooltip');
		JHtml::_('jquery.framework', false);
		//JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.glyphicons.min.css' );
		JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bootstrap-grid.min.css' );
		JHTML::stylesheet( 'media/com_phocacart/css/administrator/phocacart.css' );
		JHTML::stylesheet( 'media/com_phocacart/css/administrator/phocacarttheme.css' );
		JHTML::stylesheet( 'media/com_phocacart/css/administrator/phocacartcustom.css' );
		JHTML::stylesheet( 'media/com_phocacart/bootstrap/css/bootstrap.glyphicons-icons-only.min.css' );

		if(PhocacartUtils::isJCompatible('3.7')) {
			JHTML::stylesheet( 'media/com_phocacart/css/administrator/37.css' );
		}
		
	}
	
	public function loadOptions($load = 0) {
		if ($load == 1) {
			JHTML::stylesheet('media/com_phocacart/css/administrator/phocacartoptions.css' );
		}
	}
}
?>