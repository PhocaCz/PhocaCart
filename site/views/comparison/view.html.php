<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
jimport( 'joomla.application.component.view');

class PhocaCartViewComparison extends HtmlView
{
	protected $t;
	protected $r;
	protected $p;
	protected $s;

	function display($tpl = null)
	{
		$app								= Factory::getApplication();
		//$model								= $this->getModel();
		//$document							= JFactory::getDocument();
		$this->s                            = PhocacartRenderStyle::getStyles();
		$this->p 							= $app->getParams();

		//$this->t['categories']				= $model->getCategoriesList();

		$this->t['cart_metakey'] 			= $this->p->get( 'cart_metakey', '' );
		$this->t['cart_metadesc'] 			= $this->p->get( 'cart_metadesc', '' );
		$this->t['unit_size']			    = $this->p->get( 'unit_size', 0 );
		$this->t['unit_weight']			    = $this->p->get( 'unit_weight', 0 );
		$this->t['unit_volume']			    = $this->p->get( 'unit_volume', 0 );
        $this->t['display_webp_images']		= $this->p->get( 'display_webp_images', 0 );

		$rights							= new PhocacartAccessRights();
		$this->t['can_display_price']	= $rights->canDisplayPrice();

		$uri 						= Uri::getInstance();
		$this->t['action']			= $uri->toString();
		$this->t['actionbase64']	= base64_encode($this->t['action']);
		$this->t['linkcomparison']	= Route::_(PhocacartRoute::getComparisonRoute());

		$compare = new PhocacartCompare();
		$this->t['items'] = $compare->getFullItems();

		// Will the values be displayed or not - if the value exists at least by one product, display it
		$this->t['value']['length'] = 0;
		$this->t['value']['width'] = 0;
		$this->t['value']['height'] = 0;
		$this->t['value']['weight'] = 0;
		$this->t['value']['volume'] = 0;
		$this->t['value']['attrib'] = 0;
		$this->t['value']['stock'] = 0;

		$this->t['spec'] = array();

		if (!empty($this->t['items'])) {

			foreach ($this->t['items'] as $k => $v) {

				if($v['length'] > 0) {$this->t['value']['length'] = 1;}
				if($v['width'] > 0) {$this->t['value']['width'] = 1;}
				if($v['height'] > 0) {$this->t['value']['height'] = 1;}
				if($v['weight'] > 0) {$this->t['value']['weight'] = 1;}
				if($v['volume'] > 0) {$this->t['value']['volume'] = 1;}

				$this->t['items'][$k]['attr_options']= PhocacartAttribute::getAttributesAndOptions((int)$v['id']);
				if (!empty($this->t['items'][$k]['attr_options'])) {
					$this->t['value']['attrib'] = 1;
				}

				$this->t['items'][$k]['specifications']= PhocacartSpecification::getSpecificationGroupsAndSpecifications((int)$v['id']);
				if (!empty($this->t['items'][$k]['specifications'])) {
					foreach($this->t['items'][$k]['specifications'] as $k2 => $v2) {
						//$this->t['spec'][$k2] = $v2[0];
						$newV2 = $v2;
						unset($newV2[0]);
						if (!empty($newV2)) {
							foreach($newV2 as $k3 => $v3) {
								//$this->t['spec'][$v2[0]][$v3['title']][$k] = $v3['value'];
								//$this->t['spec'][$k2][$k3][$k3] = $v3['value'];
								$this->t['spec'][$v2[0]][$v3['title']][$k][$k3] = $v3['value'];
							}
						}

					}
				}

				$stockStatus = PhocacartStock::getStockStatus((int)$v['stock'], (int)$v['min_quantity'], (int)$v['min_multiple_quantity'], (int)$v['stockstatus_a_id'],  (int)$v['stockstatus_n_id']);
				$this->t['items'][$k]['stock'] = PhocacartStock::getStockStatusOutput($stockStatus);
				if ($this->t['items'][$k]['stock'] != '') {
					$this->t['value']['stock'] = 1;
				}
			}
		}

		$media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
		$media->loadSpec();

		$this->t['pathitem'] = PhocacartPath::getPath('productimage');
		$this->_prepareDocument();
		parent::display($tpl);

	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_COMPARISON'));
	}
}
?>
