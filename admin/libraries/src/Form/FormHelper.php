<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Form;

defined('_JEXEC') or die;

abstract class FormHelper
{
    public static function parseShowOnConditions($showOn, $formControl = null, $group = null)
    {
        // Process the showon data.
        if (!$showOn) {
            return [];
        }

        $formPath = $formControl ?: '';

        if ($group) {
            $groups = explode('.', $group);

            // An empty formControl leads to invalid shown property
            // Use the 1st part of the group instead to avoid.
            if (empty($formPath) && isset($groups[0])) {
                $formPath = $groups[0];
                array_shift($groups);
            }

            foreach ($groups as $group) {
                $formPath .= '[' . $group . ']';
            }
        }

        $showOnData  = [];
        $showOnParts = preg_split('#(\[AND\]|\[OR\])#', $showOn, -1, PREG_SPLIT_DELIM_CAPTURE);
        $op          = '';

        foreach ($showOnParts as $showOnPart) {
            if (($showOnPart === '[AND]') || $showOnPart === '[OR]') {
                $op = trim($showOnPart, '[]');
                continue;
            }

            $compareEqual     = strpos($showOnPart, '!:') === false;
            $showOnPartBlocks = explode(($compareEqual ? ':' : '!:'), $showOnPart, 2);

            $dotPos = strpos($showOnPartBlocks[0], '.');

            if (strpos($showOnPartBlocks[0], '#') === 0) {
                $formPath = 'jform';
                $showOnPartBlocks[0] = substr($showOnPartBlocks[0], 1);
            }

            while (strpos($showOnPartBlocks[0], '%') === 0) {
                $formPath = explode('][', $formPath);
                array_pop($formPath);
                $formPath = implode('][', $formPath) . ']';
                $showOnPartBlocks[0] = substr($showOnPartBlocks[0], 1);
            }

            if ($dotPos === false) {
                $field = $formPath ? $formPath . '[' . $showOnPartBlocks[0] . ']' : $showOnPartBlocks[0];
            } else {
                if ($dotPos === 0) {
                    $fieldName = substr($showOnPartBlocks[0], 1);
                    $field     = $formControl ? $formControl . '[' . $fieldName . ']' : $fieldName;
                } else {
                    if ($formControl) {
                        $field = $formControl . ('[' . str_replace('.', '][', $showOnPartBlocks[0]) . ']');
                    } else {
                        $groupParts = explode('.', $showOnPartBlocks[0]);
                        $field      = array_shift($groupParts) . '[' . join('][', $groupParts) . ']';
                    }
                }
            }

            $showOnData[] = [
                'field'  => $field,
                'values' => explode(',', $showOnPartBlocks[1]),
                'sign'   => $compareEqual === true ? '=' : '!=',
                'op'     => $op,
            ];

            if ($op !== '') {
                $op = '';
            }
        }

        return $showOnData;
    }
}
