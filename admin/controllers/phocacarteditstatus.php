<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';

class PhocaCartCpControllerPhocaCartEditStatus extends PhocaCartCpControllerPhocaCartCommon
{
	public function &getModel($name = 'PhocaCartEditStatus', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	function editstatus() {
        $app	= Factory::getApplication();

		if (!Session::checkToken('request')) {
			$app->enqueueMessage('Invalid Token', 'message');
			return false;
		}

        $model = $this->getModel('phocacarteditstatus');
        $data    = $this->input->post->get('jform', [], 'array');

        $form = $model->getForm($data, false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');
            return false;
        }

        $validData = $model->validate($form, $data);

        if ($validData['id']) {
            if ($model->editStatus($validData)) {
                $app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_UPDATE_STATUS'));
            } else {
                $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_UPDATE_STATUS'), 'error');
            }
            $app->redirect('index.php?option=com_phocacart&view=phocacarteditstatus&tmpl=component&id=' . $validData['id']);
        } else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_NO_ITEM_FOUND'), 'error');
            $app->redirect('index.php?option=com_phocacart&view=phocacarteditstatus&tmpl=component');
        }
	}

	function emptyhistory() {
		$app	= Factory::getApplication();
		$jform	= $app->input->get('jform', array(), 'array');

		if ((int)$jform['id'] > 0) {
			$model = $this->getModel( 'phocacarteditstatus' );

			if(!$model->emptyHistory($jform['id'])) {
				$message = Text::_( 'COM_PHOCACART_ERROR_EMPTY_STATUSES' );
				$app->enqueueMessage($message, 'error');
			} else {
				$message = Text::_( 'COM_PHOCACART_SUCCESS_EMPTY_STATUSES' );
				$app->enqueueMessage($message, 'message');
			}
			$app->redirect('index.php?option=com_phocacart&view=phocacarteditstatus&tmpl=component&id='.(int)$jform['id']);
		} else {

			$app->enqueueMessage(Text::_('COM_PHOCACART_NO_ITEM_FOUND'), 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacarteditstatus&tmpl=component');
		}
	}
}

