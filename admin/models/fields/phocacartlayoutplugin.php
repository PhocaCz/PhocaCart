<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

class JFormFieldPhocaCartLayoutPlugin extends FormField
{
	protected $type 		= 'PhocaCartLayoutPlugin';

	protected function getInput() {


		$typeview = $this->element['typeview'] ? (string)$this->element['typeview'] : '';

		$plugin = array();
		$plugin['name'] = '';
		$plugin['group'] = 'pcl';
		$plugin['title'] = 'Phoca Cart Layout';
		$plugin['selecttitle'] = Text::_('COM_PHOCACART_SELECT_LAYOUT_PLUGIN');
		$plugin['returnform'] = 1;

		$plugins 	= PhocacartPlugin::getPluginMethods($plugin);
		$pluginsA 	= array();
		$i = 0;
		if (!empty($plugins)) {
			foreach ($plugins as $k => $v) {
				if (isset($v['value']) && $v['value'] == '' && $i == 0) {
					// Filter to "Select Option" Text
					$pluginsA[] = $v;
				} else if (isset($v['value'])) {
					// Filter to typeview plugins only (e.g. items)
					$pluginPart = explode('_', $v['value']);
					if (isset($pluginPart[0]) && $pluginPart[0] == $typeview) {
						$pluginsA[] = $v;
					}
				}
				$i++;
			}
		}

		return HTMLHelper::_('select.genericlist',  $pluginsA,  $this->name, 'class="form-select"', 'value', 'text', $this->value, $this->id );
	}
}
?>
