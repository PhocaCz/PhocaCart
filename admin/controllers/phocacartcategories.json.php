<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Phoca\PhocaCart\MVC\Controller\Ajax\FeaturedControllerTrait;
use Phoca\PhocaCart\MVC\Controller\Ajax\StateControllerTrait;

require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';

class PhocaCartCpControllerPhocaCartCategories extends PhocaCartCpControllerPhocaCartCommons
{
    use StateControllerTrait, FeaturedControllerTrait;

    public function __construct($config = [], \Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null, ?\Joomla\CMS\Application\CMSWebApplicationInterface $app = null, ?\Joomla\Input\Input $input = null)
    {
        $this->featuredController = 'phocacartcategories';
        $this->featuredAuthorise = 'phocacartcategory';

        parent::__construct($config, $factory, $app, $input);
    }

    public function &getModel($name = 'PhocaCartCategory', $prefix = 'PhocaCartCpModel', $config = array())
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}

