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

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Json;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\String\Inflector;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The products controller
 *
 * @since  4.1.0
 */
class ProductsController extends BaseApiController
{
    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected $contentType = 'products';

    /**
     * @inheritdoc
     * @since  4.1.0
     */
    protected $default_view = 'products';

    public function getModel($name = '', $prefix = '', $config = [])
    {
        if ($name === 'products')
            $name = 'items';

        if ($name === 'product')
            $name = 'item';

        return parent::getModel($name, $prefix, $config);
    }

    public function displayList()
    {
        $app = Factory::getApplication();

        if ($lang = $app->input->getCmd('language')) {
            $app->setUserState('com_phocacart.phocacartitems.filter.language', $lang);
        }

        if ($sku = $app->input->getCmd('sku')) {
            $app->setUserState('com_phocacart.phocacartitems.filter.sku', $sku);
        }

        if ($gtin = $app->input->getCmd('gtin')) {
            $app->setUserState('com_phocacart.phocacartitems.filter.gtin', $gtin);
        }

        return parent::displayList();
    }

    private function displayResults(array $results): void
    {
        $viewType   = $this->app->getDocument()->getType();
        $viewName   = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        try {
            /** @var \Joomla\Component\PhocaCart\Api\View\Products\JsonApiView $view */
            $view = $this->getView(
                $viewName,
                $viewType,
                '',
                ['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
            );
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $view->document = $this->app->getDocument();

        $view->displayResults($results);
    }

    public function editMulti()
    {
        $this->loadFormsPath();

        /** @var \Joomla\CMS\MVC\Model\AdminModel $model */
        $model = $this->getModel(Inflector::singularize($this->contentType));

        if (!$model) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
        }

        try {
            $table = $model->getTable();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $key = $table->getKeyName();

        /** @var Json $this->input->json */
        $data = json_decode($this->input->json->getRaw(), true);
        $results = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                $results[] = (object)[
                    'success' => false,
                    'error' => 'Invalid data',
                ];
                continue;
            }

            if (!isset($item[$key])) {
                $results[] = (object)[
                    'success' => false,
                    'error' => 'Missing ID',
                ];
                continue;
            }

            $recordId = (int)$item[$key];

            if (!$recordId) {
                $results[] = (object)[
                    'success' => false,
                    'error' => 'Invalid ID',
                ];
                continue;
            }

            // Access check.
            if (!$this->allowEdit([$key => $recordId], $key)) {
                $results[] = (object)[
                    'id' => $recordId,
                    'success' => false,
                    'error' => 'Not allowed',
                ];
                continue;
            }

            $this->input->set('data', $item);
            try {
                $this->save($recordId);
                $results[] = (object)[
                    'id' => $recordId,
                    'success' => true,
                ];
            } catch (\Exception $e) {
                $results[] = (object)[
                    'id' => $recordId,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->displayResults($results);

        return $this;
    }

    public function updateStock()
    {
        // Access check.
        if (!$this->allowEdit()) {
            throw new NotAllowed('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED', 403);
        }

        /** @var Json $this->input->json */
        $data = json_decode($this->input->json->getRaw(), true);
        if ($data === null) {
            // TODO InvalidInputException
            echo $this->input->json->getRaw();
            die;
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $results = [];

        /*
        // Delete stock for advanced stock management
        $query = $db->getQuery(true)
            ->update('#__phocacart_product_stock')
            ->set([
                $db->quoteName('stock') . ' = 0',
            ]);

        $db->setQuery($query);
        $db->execute();

        // Delete stock for products
        $query = $db->getQuery(true)
            ->update('#__phocacart_products')
            ->set([
                $db->quoteName('stock') . ' = 0',
            ]);

        $db->setQuery($query);
        $db->execute();
        */

        foreach ($data as $index => $item) {
            if (!is_array($item)) {
                $results[] = (object)[
                    'id' => $index,
                    'success' => false,
                    'error' => 'Invalid data',
                ];
                continue;
            }

            if ((!isset($item['sku']) || empty($item['sku'])) && (!isset($item['gtin']) || empty($item['gtin']))) {
                $results[] = (object)[
                    'id' => $index,
                    'success' => false,
                    'error' => 'Missing SKU or GTIN',
                ];
                continue;
            }

            if (!isset($item['stock'])) {
                $results[] = (object)[
                    'id' => $index,
                    'success' => false,
                    'error' => 'Missing stock situation',
                ];
                continue;
            }

            try {
                if (isset($item['sku']) && !empty($item['sku'])) {
                    $where = [
                        $db->quoteName('sku') . ' = ' . $db->quote($item['sku']),
                    ];
                } else {
                    $where = [
                        $db->quoteName('gtin') . ' = ' . $db->quote($item['gtin']),
                    ];
                }

                // Update stock for advanced stock management
                $query = $db->getQuery(true)
                    ->update('#__phocacart_product_stock')
                    ->set([
                        $db->quoteName('stock') . ' = ' . (int)$item['stock'],
                    ])
                    ->where($where);

                $db->setQuery($query);
                $db->execute();
                $count = $db->getAffectedRows();

                // Update stock for products
                $query = $db->getQuery(true)
                    ->update('#__phocacart_products')
                    ->set([
                        $db->quoteName('stock') . ' = ' . (int)$item['stock'],
                    ])
                    ->where($where);

                $db->setQuery($query);
                $db->execute();
                $count += $db->getAffectedRows();

                $results[] = (object)[
                    'id' => $index,
                    'success' => true,
                    'count' => $count,
                ];
            } catch (\Exception $e) {
                $results[] = (object)[
                    'id' => $index,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->displayResults($results);

        return $this;
    }

}
