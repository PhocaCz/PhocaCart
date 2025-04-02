<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
namespace Joomla\Component\PhocaCart\Api\Controller;

use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base Api controller
 *
 * @since  4.1.0
 */
abstract class BaseApiController extends ApiController
{
    protected function loadFormsPath()
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_phocacart/models/forms');
        Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_phocacart/models/fields');
    }

    public function edit()
    {
        $this->loadFormsPath();
        return parent::edit();
    }

    public function getModel($name = '', $prefix = '', $config = [])
    {
        BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/models');
        return BaseDatabaseModel::getInstance('PhocaCart' . $name, 'PhocaCartCpModel', $config);
    }
}
