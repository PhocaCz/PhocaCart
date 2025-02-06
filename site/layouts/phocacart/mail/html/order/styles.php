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
    'hidden' => 'display: none;',
];
?>
<style type="text/css">
    .ph__items {

    }

    @media screen and (max-width: 596px) {
        .ph__block-s {
            display: block;
        }

        .ph__inline-s {
            display: inline-block;
        }
    }
</style>
