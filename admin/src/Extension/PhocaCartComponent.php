<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Joomla\Component\PhocaCart\Administrator\Extension;

use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Extension\LegacyComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Event\Event;

\defined('JPATH_PLATFORM') or die;

/**
 * Component class for com_phocacart
 *
 * @since  4.1.0
 */
class PhocaCartComponent extends LegacyComponent implements CategoryServiceInterface
{
  /**
   * @inheritdoc
   * @since   4.1.0
   */
  public function __construct(string $component = 'com_phocacart')
  {
    parent::__construct($component);
    Factory::getApplication()->getDispatcher()->addListener('onContentPrepareForm', [$this, 'onContentPrepareForm']);
  }

  /**
   * @inheritdoc
   * @since   4.0.0
   */
  public function getCategory(array $options = [], $section = ''): CategoryInterface
  {

        // Hide wrong information about Phoca Cart categories in custom field list
        $app    = Factory::getApplication();
        $option = $app->input->get('option', '', 'string');
        $context   = $app->input->get('context', '', 'string');
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
  public function countItems(array $items, string $section)
  {
  }

  /**
   * @inheritdoc
   * @since   4.1.0
   */
  public function prepareForm(Form $form, $data)
  {
  }

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
}
