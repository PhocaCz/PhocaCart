<?php
defined('_JEXEC') or die;

JLoader::registerNamespace('\\PhocaCart', __DIR__ . '/src');
JLoader::registerNamespace('\\Phoca', __DIR__ . '/Phoca');
JLoader::registerNamespace('\\Mike42\\Escpos', __DIR__ . '/Escpos');
JLoader::register('Parsedown', __DIR__ . '/Parsedown/Parsedown.php');

JLoader::registerPrefix('Phocacart', __DIR__ . '/phocacart');
//require_once __DIR__ . '/classmap.php';
