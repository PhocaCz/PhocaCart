<?php



defined('_JEXEC') or die();
use Joomla\CMS\Component\Router\Rules\MenuRules;


use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\Registry\Registry;

class PhocaCartRouterrules extends StandardRules
{
	public function preprocess(&$query)
	{

		parent::preprocess($query);


	}

	protected function buildLookup($language = '*')
	{
		parent::buildLookup($language);

	}
}
