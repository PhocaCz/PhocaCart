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

class PhocaCartCpViewPhocacartCatalogs extends JViewLegacy
{

	protected $state;
	protected $t;
	protected $r;
	protected $s;
	protected $params;
	protected $items 	= array();


	function display($tpl = null) {


		$this->t				= PhocacartUtils::setVars('catalog');
		$this->s                = PhocacartRenderStyle::getStyles();
		$this->state			= $this->get('State');
		$this->params			= PhocacartUtils::getComponentParameters();
		$app				= JFactory::getApplication();
		$this->t['format']	= $app->input->get('format', '', 'string');
		$cid				= $app->input->get('cid', '', 'string');


		$cidA = array_map('intval', explode(',', $cid));

		$this->items = PhocacartProduct::getProductsByCategories($cidA);

		$this->document->setName(JText::_('COM_PHOCACART_CATALOG'));

		parent::display();
	}
}
?>
