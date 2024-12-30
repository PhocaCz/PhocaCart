<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Component\Router\Rules\MenuRules;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;


use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\Registry\Registry;

class PhocacartRouterrules extends MenuRules
{
    public function preprocess(&$query) {

        // BE AWARE TODO - change in Joomla 6
        // Temporary solution as Joomla core includes unsoved bug:

        //parent::preprocess($query);

        $active = $this->router->menu->getActive();

        /**
         * If the active item id is not the same as the supplied item id or we have a supplied item id and no active
         * menu item then we just use the supplied menu item and continue
         */
        if (isset($query['Itemid']) && ($active === null || $query['Itemid'] != $active->id)) {
            return;
        }

        // Get query language
        $language = $query['lang'] ?? '*';

        // Set the language to the current one when multilang is enabled and item is tagged to ALL
        if (Multilanguage::isEnabled() && $language === '*') {
            $language = $this->router->app->get('language');
        }

        if (!isset($this->lookup[$language])) {
            $this->buildLookup($language);
        }

        // Check if the active menu item matches the requested query
        if ($active !== null && isset($query['Itemid'])) {
            // Check if active->query and supplied query are the same
            $match = true;

            foreach ($active->query as $k => $v) {
                if (isset($query[$k]) && $v !== $query[$k]) {
                    // Compare again without alias
                    if (\is_string($v) && $v == current(explode(':', $query[$k], 2))) {
                        continue;
                    }

                    $match = false;
                    break;
                }
            }

            if ($match) {
                // Just use the supplied menu item
                return;
            }
        }

        $needles = $this->router->getPath($query);

        $layout = isset($query['layout']) && $query['layout'] !== 'default' ? ':' . $query['layout'] : '';

        if ($needles) {

            foreach ($needles as $view => $ids) {

                $viewLayout = $view . $layout;

                if ($layout && isset($this->lookup[$language][$viewLayout])) {
                    if (\is_bool($ids)) {
                        $query['Itemid'] = $this->lookup[$language][$viewLayout];

                        return;
                    }

                    foreach ($ids as $id => $segment) {
                        if (isset($this->lookup[$language][$viewLayout][(int) $id])) {
                            $query['Itemid'] = $this->lookup[$language][$viewLayout][(int) $id];

                            return;
                        }
                    }
                }

                if (isset($this->lookup[$language][$view])) {
                    if (\is_bool($ids)) {
                        $query['Itemid'] = $this->lookup[$language][$view];

                        return;
                    }
                    foreach ($ids as $id => $segment) {

                        if (isset($this->lookup[$language][$view][(int) $id])) {
                            $query['Itemid'] = $this->lookup[$language][$view][(int) $id];

                            return;
                        }
                    }
                }
            }
        }

        // TODO: Remove this whole block in 6.0 as it is a bug
        /*if (!$this->sefparams->get('strictrouting', 0)) {
            // Check if the active menuitem matches the requested language
            if (
                $active && $active->component === 'com_' . $this->router->getName()
                && ($language === '*' || \in_array($active->language, ['*', $language]) || !Multilanguage::isEnabled())
            ) {
                $query['Itemid'] = $active->id;

                return;
            }

            // If not found, return language specific home link
            $default = $this->router->menu->getDefault($language);

            if (!empty($default->id)) {
                $query['Itemid'] = $default->id;
            }
        }*/
    }

    protected function buildLookup($language = '*') {
        parent::buildLookup($language);
    }

    /* EDIT of libraries/src/Component/Router/Rules/StandardRules.php build function
     * Because we need to manage when categories view does not have any ID
     * PHOCAEDIT
     */
    public function build(&$query, &$segments) {
        if (!isset($query['Itemid'], $query['view'])) {
            return;
        }

        // Get the menu item belonging to the Itemid that has been found
        $item = $this->router->menu->getItem($query['Itemid']);


        // PHOCAEDIT
        if (isset($item->query) && !isset($item->query['id'])) {
            $item->query['id'] = 0;
        }

        if ($item === null
            || $item->component !== 'com_' . $this->router->getName()
            || !isset($item->query['view'])) {
            return;
        }

        // Get menu item layout
        $mLayout = isset($item->query['layout']) ? $item->query['layout'] : null;

        // Get all views for this component
        $views = $this->router->getViews();

        // Return directly when the URL of the Itemid is identical with the URL to build
        if ($item->query['view'] === $query['view']) {
            $view = $views[$query['view']];

            if (!$view->key) {
                unset($query['view']);

                if (isset($query['layout']) && $mLayout === $query['layout']) {
                    unset($query['layout']);
                }

                // PHOCAEDIT - under review
                // if we have category view but not categories view
                // then some menu links like checkout, download, etc. get suffix id=0
                // Remove it (still under review as some parts in Joomla can demand id=0
                // to not throw error
                if (isset($query['id']) && $query['id'] == 0) {
                    unset($query['id']);
                }

                return;
            }

            if (isset($query[$view->key]) && $item->query[$view->key] == (int)$query[$view->key]) {
                unset($query[$view->key]);

                while ($view) {
                    unset($query[$view->parent_key]);

                    $view = $view->parent;
                }

                unset($query['view']);

                if (isset($query['layout']) && $mLayout === $query['layout']) {
                    unset($query['layout']);
                }

                return;
            }
        }

        // Get the path from the view of the current URL and parse it to the menu item
        $path  = array_reverse($this->router->getPath($query), true);
        $found = false;

        foreach ($path as $element => $ids) {
            $view = $views[$element];

            if ($found === false && $item->query['view'] === $element) {
                if ($view->nestable) {
                    $found = true;
                } else if ($view->children) {
                    $found = true;

                    continue;
                }
            }

            if ($found === false) {
                // Jump to the next view
                continue;
            }


            if ($ids) {
                if ($view->nestable) {
                    $found2 = false;

                    foreach (array_reverse($ids, true) as $id => $segment) {


                        if ($found2) {
                            $segments[] = str_replace(':', '-', $segment);
                        } else if ((int)$item->query[$view->key] === (int)$id) {
                            $found2 = true;
                        }
                    }
                } else if ($ids === true) {
                    $segments[] = $element;
                } else {
                    $segments[] = str_replace(':', '-', current($ids));
                }
            }

            if ($view->parent_key) {
                // Remove parent key from query
                unset($query[$view->parent_key]);
            }
        }

        if ($found) {
            unset($query[$views[$query['view']]->key], $query['view']);

            if (isset($query['layout']) && $mLayout === $query['layout']) {
                unset($query['layout']);
            }
        }

    }
}
