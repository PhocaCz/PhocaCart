<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');

class PhocaCartViewTerms extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;
	public function display($tpl = null) {
	
		$app						= JFactory::getApplication();
		$this->p 					= $app->getParams();
		$this->t['terms_conditions']= $this->p->get( 'terms_conditions', '' );
		
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		
		echo '<div class="ph-terms-box-in">';
		echo $this->t['terms_conditions'];
		echo '</div>';
		
	}
	
	protected function _prepareDocument() {
		PhocaCartRenderFront::prepareDocument($this->document, $this->p);
	}
}
?>