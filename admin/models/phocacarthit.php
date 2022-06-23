<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocaCartHit extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	public $typeAlias 			= 'com_phocacart.phocacarthit';
	
	protected function canDelete($record){
		$user = Factory::getUser();

		if (!empty($record->catid)) {
			// catid not used
			return $user->authorise('core.delete', 'com_phocacart.phocacarthit.'.(int) $record->catid);
		} else {
			return parent::canDelete($record);
		}
	}
	
	public function getForm($data = array(), $loadData = true) {
		return false;
	}
	
	
	public function delete(&$cid = array()) {
		
		if (count( $cid )) {
			ArrayHelper::toInteger($cid);
			$error 	= 0;
			if (!empty($cid)) {
				foreach ($cid as $k => $v) {
					$q = 'DELETE FROM #__phocacart_hits'
					. ' WHERE id = '.(int)$v;
					$this->_db->setQuery( $q );
					$this->_db->execute();
				}
			}
		}
		return true;
	}
}
?>
