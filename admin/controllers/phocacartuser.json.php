<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class PhocaCartCpControllerPhocacartUser extends FormController
{
	public function setregion() {
	
		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="alert alert-danger">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			exit;
		}
		
		$app	= Factory::getApplication();
		$id		= $app->input->get('countryid', 0, 'int');
		
		//$model = $this->getModel('checkout');
		//$options = $model->getRegions($id);
		$options = PhocacartRegion::getRegionsByCountry($id);
		$o = '';
		if(!empty($options)) {
			
			$o .= '<option value="">-&nbsp;'.Text::_('COM_PHOCACART_SELECT_REGION').'&nbsp;-</option>';
			foreach($options as $k => $v) {
				$o .= '<option value="'.$v->id.'">'.htmlspecialchars($v->title).'</option>';
			}
		}
		$response = array(
				'status' => '1',
				'content' => $o);
			echo json_encode($response);
			exit;
		
	}
}
?>