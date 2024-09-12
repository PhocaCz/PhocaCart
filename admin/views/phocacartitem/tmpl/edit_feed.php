<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

$fieldSets = $this->form->getFieldsets();

$o = '';

foreach($fieldSets as $name => $fieldSet) {

    // Manage only Phoca Cart Feed plugins
    if (isset($fieldSet->group) && $fieldSet->group == 'pcf') {

        foreach ($this->form->getFieldset($name) as $field) {

            // We use subforms to load all feeds at once and to store them only to one column in product table
            // Example: Plugin zbozi_cz plugins\pcf\zbozi_cz\models\forms\item.xml
            // is linked by dynamically created form in administrator/components/com_phocacart/models/phocacartitem.php in preprocessForm() function
            // In preprocssForm we build the XML Form inclusive the subform XML of all feed plugins
            // And here we need to fill the form with stored data (column in product table is called params_feed
            if ($field->type == 'Subform') {

                if (File::exists($field->formsource)) {
                    $subform  = $field->loadSubform();          // e.g. zbozi_cz
                    $nameFeed = str_replace('feed_', '', $name);// we have created dynamically the field as feed_zbozi_cz to differentiate from other fields, now return back to plugin name

                    Dispatcher::dispatch(new Event\Feed\Category\BeforeRender('com_phocacart.item', $nameFeed, $subform));

                    if (isset($this->item->params_feed[$nameFeed])) {
                        $subform->bind($this->item->params_feed[$nameFeed]);// bind the data from $this->item->param_feed['zbozi_cz'] to the subform
                    }

                    $o .= '<div class="control-group">';
                    $o .= '<div class="control-label">' . $field->label . '</div>';
                    $o .= '<div class="controls">' . $field->input . '</div>';
                    $o .= '</div>';
                }

            }
        }
    }
}

if ($o != '') {
    echo $o;
} else {
    echo '<div class="alert alert-info">'.Text::_('COM_PHOCACART_NO_ACTIVE_FEED_PLUGIN_FOUND').'</div>';
}


