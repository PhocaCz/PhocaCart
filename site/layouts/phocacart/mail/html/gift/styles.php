<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Uri\Uri;

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
    'button' => 'display: inline-block; padding: 8 16px; color: #fff; background-color: #2e486b; border: 8px solid #2e486b; font-size: 16px; font-weight: bold; text-decoration: none;',
    'ph-gift-voucher-box' => 'background: #ffffff; border: 3px dashed #252A34; box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px; position: relative; padding: 0.5em;',
    'ph-gift-voucher-body' => 'background: #A3464B; width:100%;',
    'ph-gift-voucher-image' => 'width: 100%; height: 200px;',
    'ph-gift-voucher-title' => 'background: #A3464B; color: #ffffff; font-size: 4em; font-weight: bold; padding-top: 0.5em; padding-bottom: 0.5em;',
    'ph-gift-voucher-title-wrapper' => 'text-align: center;',
    'ph-gift-voucher-col1' => 'color: #ffffff; width: 30%; text-align: center; vertical-align: middle;',
    'ph-gift-voucher-col2' => 'color: #ffffff; width: 70%; text-align: left; font-size: 0.7em; padding: 5%;',
    'ph-gift-voucher-head' => 'background: #ffffff; border-radius: 50%; width: 10em; height: 10em; margin: auto;',
    'ph-gift-voucher-head-top' => 'color: #A3464B; font-weight: bold; text-transform: uppercase; font-size: 2em; text-align: center;padding-top:1em;',
    'ph-gift-voucher-head-bottom' => 'color: #272728; font-weight: bold; text-transform: uppercase; font-size: 1.3em; text-align: center;',
    'ph-gift-voucher-price' => 'color: #ffffff; text-align: center; font-weight: bold; font-size: 2.6em;margin: 0.2em 0;',
    'ph-gift-voucher-code' => 'color: #272728;background-color: #ffffff; text-align: center; font-weight: bold; font-size: 2.6em;margin: 0.2em 0; padding:0.5em;',
    'ph-gift-voucher-from' => '',
    'ph-gift-voucher-to' => '',
    'ph-gift-voucher-date-to' => '',
    'ph-gift-voucher-message' => '',
    'ph-gift-voucher-description' => '',
];

/* Examples of custom styling */
$styles = array_merge($styles, [
    /* gift class eats */
    'eats' => [
      'ph-gift-voucher-body' => 'background: #7A5E51;',
      'ph-gift-voucher-head-top' => 'color: #7A5E51;',
      'ph-gift-voucher-head-bottom' => 'color: #272728;',
      'ph-gift-voucher-code' => 'color: #272728;',
    ],
    /* gift class moments */
    'moments' => [
      'ph-gift-voucher-body-moments' => 'background: #F39A3D;',
      'ph-gift-voucher-head-top-moments' => 'color: #F39A3D;',
      'ph-gift-voucher-head-bottom-moments' => 'color: #272728;',
      'ph-gift-voucher-code-moments' => 'color: #272728;',
    ],
    /* gift class student */
    'student' => [
      'ph-gift-voucher-body-student' => 'background: #745a75;',
      'ph-gift-voucher-head-top-student' => 'color: #745a75;',
      'ph-gift-voucher-head-bottom-student' => 'color: #272728;',
      'ph-gift-voucher-code-student' => 'color: #272728;',
    ],
]);

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
