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
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

class PhocacartUtilsOptions
{
	private static $options = array();

	private function __construct(){}


	/*
	 * Load Phoca Cart Options only once per site
	 * Options can be different when they are called in administration or in site, or on com_phocacart site or not com_phocacart site
	 * Component = PC (Phoca Cart)
	 * Client = A (Administrator) | S (Site) | P (API)
	 * Option = com_phocacart
	 *
	 * Possible values:
	 * PCAcom_phocacart - Phoca Cart component, Administrator, option=com_phocacart
	 * PCScom_phocacart - Phoca Cart component, Site, option=com_phocacart
	 * PCScom_content - Phoca Cart component, Site, option=com_content (we need options for Phoca Cart module displayed on com_content page)
	 * (there can be some exceptions like if we call router where the option is not known yet)
	 *
	 * PhocaCartUtils::getComponentParameters -> PhocaCartUtilsOptions::getOptions (singleton)
	 *
	 * TODO parameter $component not needed, neither $client ($app->getName())
	 */

	public static function getOptions($component, $client, $option) {

		$elementOption = $component . $client . $option;

		if( !array_key_exists( $elementOption, self::$options ) ) {
			switch ($client) {
				case 'site':
					if ($option == 'com_phocacart')
						self::$options[$elementOption] =  Factory::getApplication()->getParams();
					else
						self::$options[$elementOption] = ComponentHelper::getParams('com_phocacart');
					break;
				default:
					self::$options[$elementOption] = ComponentHelper::getParams('com_phocacart');
					break;
			}
		}

		return self::$options[$elementOption];
	}

	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
	}
}

