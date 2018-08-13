<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.controller' );

class PhocaCartController extends JControllerLegacy
{	
	
	public function display($cachable = false, $urlparams = false)
	{
		
		if ( ! JFactory::getApplication()->input->get('view') ) {
			JFactory::getApplication()->input->set('view', 'categories' );
		}
		/*if (JFactory::getApplication()->input->get('view') && JFactory::getApplication()->input->get('view') == 'feed') {
			// Default view for Feed is XML
			// Don't forget, this settings needs to have set router.php too - in method PhocacartParseRoute()
			$this->getView('feed', 'xml');
		}*/
		
		
		//$paramsC 	= JComponentHelper::getParams('com_phocacart');
		$app		= JFactory::getApplication();
		$paramsC 	= $app->getParams();

		$cache 		= $paramsC->get( 'enable_cache', 0 );
		$cachable 	= false;
		if ($cache == 1) {
			$cachable 	= true;
		}
		
		$document 	= JFactory::getDocument();

		$safeurlparams = array('catid'=>'INT','id'=>'INT','cid'=>'ARRAY','year'=>'INT','month'=>'INT','limit'=>'INT','limitstart'=>'INT',
			'showall'=>'INT','return'=>'BASE64','filter'=>'STRING','filter_order'=>'CMD','filter_order_Dir'=>'CMD','filter-search'=>'STRING','print'=>'BOOLEAN','lang'=>'CMD');

		parent::display($cachable,$safeurlparams);

		return $this;
	}
}
?>