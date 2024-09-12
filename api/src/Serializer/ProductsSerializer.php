<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

namespace Joomla\Component\PhocaCart\Api\Serializer;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Relationship;
use Tobscure\JsonApi\Resource;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Products serializer
 *
 * @since  5.0.0
 */
class ProductsSerializer extends BaseSerializer
{
    /**
     * Build products relationships by associations
     *
     * @param   \stdClass  $model  products model
     *
     * @return  Relationship
     *
     * @since 5.0.0
     */
    public function languageAssociations($model)
    {
        $resources = [];

        $serializer = new JoomlaSerializer($this->type);

        foreach ($model->associations as $association) {
            $resources[] = (new Resource($association, $serializer))
                ->addLink('self', Route::link('site', Uri::root() . 'api/index.php/v1/phocacart/products/' . $association->id));
        }

        $collection = new Collection($resources, $serializer);

        return new Relationship($collection);
    }

    /**
     * Build category relationship
     *
     * @param   \stdClass  $model  products model
     *
     * @return  Relationship
     *
     * @since 5.0.0
     */
    public function category($model)
    {
        $serializer = new JoomlaSerializer('categories');

        $resource = (new Resource($model->catid, $serializer))
            ->addLink('self', Route::link('site', Uri::root() . 'api/index.php/v1/phocacart/categories/' . $model->catid));

        return new Relationship($resource);
    }
}
