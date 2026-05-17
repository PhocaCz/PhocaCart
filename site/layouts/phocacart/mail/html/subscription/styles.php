<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var array $styles */

$styles = &$displayData['styles'];
$styles = [
    'reset' => 'box-sizing: border-box;',
    'fs-normal' => 'font-family: Arial,&quot;Helvetica Neue&quot;,Helvetica,sans-serif; font-size: 14px; font-style: normal;',
    'fs-large' => 'font-size: 16px;',
    'fs-xlarge' => 'font-size: 20px;',
    'w100' => 'width: 100%;',
    'hidden' => 'display: none; max-height: 0px; overflow: hidden;',
    'button' => 'display: inline-block; padding: 12px 24px; color: #fff; background-color: #2e486b; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; text-decoration: none;',
    'card' => 'background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin: 16px 0;',
    'label' => 'color: #6c757d; font-size: 12px; text-transform: uppercase; margin-bottom: 4px;',
    'value' => 'font-size: 16px; font-weight: 600; color: #212529;',
    'status-active' => 'background-color: #d4edda; color: #155724; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
    'status-future' => 'background-color: #e9ecef; color: #2e486b; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
    'status-expired' => 'background-color: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
    'status-on-hold' => 'background-color: #e9ecef; color: #2e486b; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
    'status-pending' => 'background-color: #e9ecef; color: #2e486b; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
    'status-failed' => 'background-color: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
    'status-in-trial' => 'background-color: #e9ecef; color: #2e486b; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
    'status-card-expired' => 'background-color: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
    'status-canceled' => 'background-color: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 4px; font-weight: bold;',
];
?>
<style type="text/css">
    @media screen and (max-width: 596px) {
        .ph__block-s {
            display: block;
        }

        .ph__inline-s {
            display: inline-block;
        }
    }
</style>
