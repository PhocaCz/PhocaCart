<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Joomla\Component\PhocaCart\Administrator\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Event\Model\AfterSaveEvent;
use Joomla\CMS\Event\Model\PrepareDataEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareSaveEvent;
use Joomla\CMS\Extension\LegacyComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceTrait;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Schemaorg\SchemaorgServiceInterface;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Schemaorg\Schema;

\defined('JPATH_PLATFORM') or die;

// Joomla 4 compatibility
if (Version::MAJOR_VERSION < 5) {
    interface CompatSchemaorgServiceInterface {}
    class_alias('\\Joomla\\Component\\PhocaCart\\Administrator\\Extension\\CompatSchemaorgServiceInterface', '\\Joomla\\CMS\\Schemaorg\\SchemaorgServiceInterface');
}

/**
 * Component class for com_phocacart
 *
 * @since  4.1.0
 */
class PhocaCartComponent extends LegacyComponent implements SchemaorgServiceInterface
{
    use MVCFactoryServiceTrait;

    /**
     * The dispatcher factory.
     *
     * @var ComponentDispatcherFactoryInterface
     *
     * @since  4.1.0
     */
    private $dispatcherFactory;

    /**
     * @inheritdoc
     * @since   4.1.0
     */
    public function __construct(ComponentDispatcherFactoryInterface $dispatcherFactory, string $component = 'com_phocacart')
    {
        parent::__construct($component);
        Factory::getApplication()->getDispatcher()->addListener('onContentPrepareForm', [$this, 'onContentPrepareForm']);
        if (Version::MAJOR_VERSION >= 5) {
            Factory::getApplication()->getDispatcher()->addListener('onSchemaBeforeCompileHead', [$this, 'onSchemaBeforeCompileHead']);
            // Need this because admin contexts differs from frontend contexts
            Factory::getApplication()->getDispatcher()->addListener('onContentPrepareData', [$this, 'onContentPrepareData']);
            Factory::getApplication()->getDispatcher()->addListener('onSchemaPrepareSave', [$this, 'onSchemaPrepareSave']);
            Factory::getApplication()->getDispatcher()->addListener('onContentAfterSave', [$this, 'onContentAfterSave']);
        }
        $this->dispatcherFactory = $dispatcherFactory;
    }

    /**
     * @inheritdoc
     * @since   4.0.0
     */
    public function getCategory(array $options = [], $section = ''): CategoryInterface
    {

        // Hide wrong information about Phoca Cart categories in custom field list
        $app     = Factory::getApplication();
        $option  = $app->input->get('option', '', 'string');
        $context = $app->input->get('context', '', 'string');
        if ($option == 'com_fields' && $context == 'com_phocacart.phocacartitem') {
            $document = $app->getDocument();
            $document->addCustomTag('<style type="text/css"> table#fieldList tr th[scope=row] div div:nth-child(3) { display:none } </style>');
        }

        return new class() implements CategoryInterface {
            public function getExtension(): string
            {
                return 'com_phocacart';
            }

            public function get($id = 'root', $forceload = false)
            {
                // fake categories to make FieldsModel happy
                $node = new CategoryNode();
                $node->addChild(new CategoryNode());

                return $node;
            }
        };
    }

    /**
     * @inheritdoc
     * @since   4.1.0
     */
    public function countItems(array $items, string $section) {}

    /**
     * @inheritdoc
     * @since   4.1.0
     */
    public function prepareForm(Form $form, $data) {}

    public function onContentPrepareForm(Event $event)
    {
        /** @var Form $form */
        $form = $event->getArgument(0);
        if ($form->getName() === 'com_fields.field.com_phocacart.phocacartitem') {
            Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_phocacart/models/fields');
            $form->setFieldAttribute('assigned_cat_ids', 'type', 'PhocaCartCategory');

            $form->loadFile(JPATH_ADMINISTRATOR . '/components/com_phocacart/models/forms/com_fields.xml');
        }
    }

    /**
     * @inheritdoc
     *
     * @since   4.1.0
     */
    public function getDispatcher(CMSApplicationInterface $application): DispatcherInterface
    {
        if ($application->isClient('api')) {
            return $this->dispatcherFactory->createDispatcher($application);
        }
        else {
            return parent::getDispatcher($application);
        }
    }

    public function getMVCFactory(): MVCFactoryInterface
    {
        if (Factory::getApplication()->isClient('api')) {
            return $this->mvcFactory;
        }
        else {
            return parent::getMVCFactory();
        }
    }

    /**
     * @inheritdoc
     *
     * @since   5.0.0
     */
    public function getSchemaorgContexts(): array
    {
        $app = Factory::getApplication();
        $app->getLanguage()->load('com_phocacart', JPATH_ADMINISTRATOR);

        if ($app->isClient('site')) {
            $contexts = [
                'com_phocacart.item' => Text::_('COM_PHOCACART_SCHEMAORG_PRODUCT'),
            ];
        } else {
            $contexts = [
                'com_phocacart.phocacartitem' => Text::_('COM_PHOCACART_SCHEMAORG_PRODUCT'),
            ];
        }

        return $contexts;
    }

    /**
     * Adds Schema.org data
     *
     * @since   5.0.0
     */
    public function onSchemaBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        if (!Factory::getApplication()->isClient('site')) {
            return;
        }

        $context = $event->getContext();
        $schema  = $event->getSchema();

        Schema::injectProductSchema($context, $schema);
    }

    /**
     * Modifies Schema.org data with proper context
     * We need this, as admin contexts are different with frontend contexts
     *
     * @since   5.0.0
     */
    public function onSchemaPrepareSave(PrepareSaveEvent $event): void
    {
        if (!Factory::getApplication()->isClient('administrator') || $event->getContext() !== 'com_phocacart.phocacartitem') {
            return;
        }

        $subject = $event->getArgument('subject');
        $subject->context = 'com_phocacart.item';

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__schemaorg'))
            ->where($db->quoteName('itemId') . '= :itemId')
            ->bind(':itemId', $subject->itemId, ParameterType::INTEGER)
            ->where($db->quoteName('context') . '= :context')
            ->bind(':context', $subject->context, ParameterType::STRING);
        $db->setQuery($query);
        $subject->id = $db->loadResult();
    }

    /**
     * Deletes obsolete Schema.org data with proper context
     * We need this, as admin contexts are different with frontend contexts
     *
     * @since   5.0.0
     */
    public function onContentAfterSave(AfterSaveEvent $event): void
    {
        if (!Factory::getApplication()->isClient('administrator') || $event->getContext() !== 'com_phocacart.phocacartitem') {
            return;
        }

        $context = 'com_phocacart.item';
        $table   = $event->getItem();
        $data    = $event->getData();
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $itemId = (int) $table->id;

        if (empty($data['schema']) || empty($data['schema']['schemaType']) || $data['schema']['schemaType'] === 'None') {
            $query = $db->getQuery(true);

            $query->delete($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . '= :itemId')
                ->bind(':itemId', $itemId, ParameterType::INTEGER)
                ->where($db->quoteName('context') . '= :context')
                ->bind(':context', $context, ParameterType::STRING);

            $db->setQuery($query)->execute();
        }
    }

    /**
     * Loads Schema.org data with proper context
     * We need this, as admin contexts are different with frontend contexts
     *
     * @since   5.0.0
     */
    public function onContentPrepareData(PrepareDataEvent $event): void
    {
        $context = $event->getContext();

        if (!Factory::getApplication()->isClient('administrator') || $context !== 'com_phocacart.phocacartitem') {
            return;
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $data = (object)$event->getData();
        if ($data->id ?? 0) {
            $context = 'com_phocacart.item';
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__schemaorg'))
                ->where($db->quoteName('itemId') . '= :itemId')
                ->bind(':itemId', $data->id, ParameterType::INTEGER)
                ->where($db->quoteName('context') . '= :context')
                ->bind(':context', $context, ParameterType::STRING);

            $results = $db->setQuery($query)->loadAssoc();

            if (empty($results)) {
                return;
            }

            $schemaType                 = $results['schemaType'];
            $data->schema['schemaType'] = $schemaType;

            $schema = new Registry($results['schema']);

            $data->schema[$schemaType] = $schema->toArray();
        }

        $dispatcher = Factory::getApplication()->getDispatcher();
        $event      = new \Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareDataEvent('onSchemaPrepareData', [
            'subject' => $data,
            'context' => $context,
        ]);

        PluginHelper::importPlugin('schemaorg', null, true, $dispatcher);
        $dispatcher->dispatch('onSchemaPrepareData', $event);
    }

}
