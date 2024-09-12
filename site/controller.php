<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
jimport( 'joomla.application.component.controller' );

class PhocaCartController extends BaseController
{

	public function display($cachable = false, $urlparams = false)
	{



		if ( ! Factory::getApplication()->input->get('view') ) {
			Factory::getApplication()->input->set('view', 'categories' );
		}

			if ( ! Factory::getApplication()->input->get('id') ) {
			Factory::getApplication()->input->set('id', null );
		}


		/*if (Factory::getApplication()->input->get('view') && Factory::getApplication()->input->get('view') == 'feed') {
			// Default view for Feed is XML
			// Don't forget, this settings needs to have set router.php too - in method PhocacartParseRoute()
			$this->getView('feed', 'xml');
		}*/


		//$paramsC 	= PhocacartUtils::getComponentParameters();
		$app		= Factory::getApplication();
		$paramsC 	= $app->getParams();

		$cache 		= $paramsC->get( 'enable_cache', 0 );
		$cachable 	= false;
		if ($cache == 1) {
			$cachable 	= true;
		}

		$document 	= Factory::getDocument();

		$safeurlparams = array('catid'=>'INT','id'=>'INT','cid'=>'ARRAY','year'=>'INT','month'=>'INT','limit'=>'INT','limitstart'=>'INT',
			'showall'=>'INT','return'=>'BASE64','filter'=>'STRING','filter_order'=>'CMD','filter_order_Dir'=>'CMD','filter-search'=>'STRING','print'=>'BOOLEAN','lang'=>'CMD');

		parent::display($cachable,$safeurlparams);

		return $this;
	}
}
?>
