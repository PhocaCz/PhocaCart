<?php
/**
 * @package   Phoca Cart
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();

/** @var array $displayData */
$styles = &$displayData['styles'];

$styles = [
    'reset'     => 'box-sizing: border-box;',
    'fs-normal' => 'font-family: Arial,"Helvetica Neue",Helvetica,sans-serif; font-size: 14px; font-style: normal;',
    'fs-large'  => 'font-size: 16px;',
    'fs-xlarge' => 'font-size: 20px;',
    'w100'      => 'width: 100%;',
    'hidden'    => 'display: none; max-height: 0px; overflow: hidden;',
    'button'    => 'display: inline-block; padding: 8px 16px; color: #fff; background-color: #2e486b; border: 8px solid #2e486b; font-size: 16px; font-weight: bold; text-decoration: none;',
    'table-cell'=> 'padding: 4px 8px; vertical-align: top;',
    'label'     => 'font-weight: bold; white-space: nowrap;',
    'link'      => 'color: #2e486b; text-decoration: underline;',
];
?>
<style type="text/css">
    @media screen and (max-width: 596px) {
        .ph__block-s { display: block; }
        .ph__inline-s { display: inline-block; }
    }
</style>
